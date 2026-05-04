<?php

namespace App\Imports;

use App\Models\GainsProfile;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class GainsProfilesImport implements ToCollection, WithHeadingRow
{
    protected array $stats = [
        'total' => 0,
        'created' => 0,
        'updated' => 0,
        'duplicates' => 0,
        'errors' => 0,
        'success' => 0,
    ];

    protected array $errors = [];

    public function __construct(
        protected bool $updateExisting = true,
        protected bool $importDriveImages = true,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $this->stats['total']++;

            try {
                $data = $this->normalizeRow($row->toArray());

                if (blank($data['full_name']) && blank($data['email']) && blank($data['phone'])) {
                    continue;
                }

                DB::transaction(function () use ($data) {
                    $profile = $this->findExistingProfile($data);

                    if ($profile && ! $this->updateExisting) {
                        $this->stats['duplicates']++;
                        return;
                    }

                    if ($profile) {
                        $this->stats['duplicates']++;

                        $this->applyProfilePayload($profile, $data);
                        $this->syncUser($data, $profile);

                        $profile->save();

                        $this->stats['updated']++;
                        $this->stats['success']++;
                    } else {
                        $user = $this->syncUser($data);

                        $profile = GainsProfile::create(array_merge(
                            $this->profilePayload($data),
                            [
                                'user_id' => $user?->id,
                                'slug' => $this->makeUniqueSlug($data['full_name']),
                                'is_public' => true,
                            ]
                        ));

                        $this->stats['created']++;
                        $this->stats['success']++;
                    }

                    if ($this->importDriveImages) {
                        $this->importDriveImages($profile, $data['personal_album_urls'], 'personal_photos');
                        $this->importDriveImages($profile, $data['business_album_urls'], 'business_photos');
                        $this->importDriveImages($profile, $data['product_album_urls'], 'product_gallery_1');
                    }
                });
            } catch (\Throwable $e) {
                $this->stats['errors']++;

                $this->errors[] = [
                    'row' => $index + 2,
                    'message' => $e->getMessage(),
                ];
            }
        }
    }

    public function stats(): array
    {
        return $this->stats;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    protected function normalizeRow(array $row): array
    {
        return [
            'full_name' => $this->pick($row, [
                'ho_va_ten',
                'họ_và_tên',
                'ho_ten',
                'họ_tên',
                'full_name',
                'name',
            ]),

            'bni_position' => $this->pick($row, [
                'chuc_vu_bni',
                'chức_vụ_bni',
                'chuc_vu_tai_bni',
                'chức_vụ_tại_bni',
                'bni_position',
            ]),

            'chapter_name' => $this->pick($row, [
                'chapter',
                'ten_chapter',
                'tên_chapter',
                'chapter_name',
            ]) ?: 'BNI KINHBAC CHAPTER',

            'date_of_birth' => $this->normalizeDate($this->pick($row, [
                'ngay_sinh',
                'ngày_sinh',
                'ngay_thang_nam_sinh',
                'ngày_tháng_năm_sinh',
                'date_of_birth',
                'birthday',
            ])),

            'address' => $this->pick($row, [
                'dia_chi',
                'địa_chỉ',
                'address',
            ]),

            'phone' => $this->normalizePhone($this->pick($row, [
                'phone',
                'so_dien_thoai',
                'số_điện_thoại',
                'dien_thoai',
                'điện_thoại',
            ])),

            'email' => strtolower(trim((string) $this->pick($row, [
                'email',
                'email_ca_nhan',
                'email_cá_nhân',
                'email_cong_viec',
                'email_công_việc',
                'dia_chi_email',
                'địa_chỉ_email',
            ]))),

            'education' => $this->pick($row, [
                'bang_cap',
                'bằng_cấp',
                'bang_capchung_chi',
                'bang_cap_chung_chi',
                'bằng_cấp_chứng_chỉ',
                'hoc_van',
                'học_vấn',
                'education',
            ]),

            'qualifications' => $this->pick($row, [
                'bang_cap',
                'bằng_cấp',
                'bang_capchung_chi',
                'bang_cap_chung_chi',
                'bằng_cấp_chứng_chỉ',
                'qualifications',
            ]),

            'personal_album_urls' => $this->extractUrls((string) $this->pick($row, [
                'album_ca_nhan',
                'album_cá_nhân',
                'anh_ca_nhan',
                'ảnh_cá_nhân',
                'personal_photos',
            ])),

            'business_album_urls' => $this->extractUrls((string) $this->pick($row, [
                'album_doanh_nghiep',
                'album_doanh_nghiệp',
                'anh_doanh_nghiep',
                'ảnh_doanh_nghiệp',
                'business_photos',
            ])),

            'product_album_urls' => $this->extractUrls((string) $this->pick($row, [
                'album_san_pham_dich_vu',
                'album_san_phamdich_vu',
                'album_sản_phẩm_dịch_vụ',
                'album_san_pham',
                'album_sản_phẩm',
                'product_photos',
                'product_gallery',
            ])),

            'company_name' => $this->pick($row, [
                'cong_ty',
                'công_ty',
                'company',
                'company_name',
                'ten_cong_ty',
                'tên_công_ty',
            ]),

            'job_title' => $this->pick($row, [
                'chuc_danh',
                'chức_danh',
                'chuc_danh_tai_doanh_nghiep',
                'chức_danh_tại_doanh_nghiệp',
                'job_title',
                'vi_tri',
                'vị_trí',
            ]),

            'business_category' => $this->pick($row, [
                'linh_vuc',
                'lĩnh_vực',
                'business_category',
                'nganh_nghe',
                'ngành_nghề',
            ]),

            'core_products' => $this->pick($row, [
                'san_pham_chinh',
                'sản_phẩm_chính',
                'core_products',
                'san_pham',
                'sản_phẩm',
            ]),

            'accompanying_services' => $this->pick($row, [
                'dich_vu_di_kem',
                'dịch_vụ_đi_kèm',
                'accompanying_services',
            ]),

            'highlight_products' => $this->pick($row, [
                'san_pham_noi_bat',
                'sản_phẩm_nổi_bật',
                'highlight_products',
            ]),

            'g_goals' => $this->pick($row, [
                'goals',
                'goals_muc_tieu',
                'goals_mục_tiêu',
                'g_goals',
                'muc_tieu',
                'mục_tiêu',
            ]),

            'a_accomplishments' => $this->pick($row, [
                'accomplishments',
                'accomplishments_thanh_tuu',
                'accomplishments_thành_tựu',
                'a_accomplishments',
                'thanh_tuu',
                'thành_tựu',
            ]),

            'i_interests' => $this->pick($row, [
                'interests',
                'interests_so_thich',
                'interests_sở_thích',
                'i_interests',
                'so_thich',
                'sở_thích',
            ]),

            'n_networks' => $this->pick($row, [
                'networks',
                'networks_mang_luoi_quan_he',
                'networks_mạng_lưới_quan_hệ',
                'networks_mang_luoi_ket_noi',
                'networks_mạng_lưới_kết_nối',
                'n_networks',
                'mang_luoi_quan_he',
                'mạng_lưới_quan_hệ',
            ]),

            's_skills' => $this->pick($row, [
                'skills',
                'skills_ky_nang',
                'skills_kỹ_năng',
                's_skills',
                'ky_nang',
                'kỹ_năng',
            ]),
        ];
    }

    protected function profilePayload(array $data, bool $onlyMissing = false): array
    {
        return [
            'full_name' => $data['full_name'],
            'education' => $data['education'],
            'qualifications' => $data['qualifications'],
            'bni_position' => $data['bni_position'],
            'chapter_name' => $data['chapter_name'],
            'date_of_birth' => $data['date_of_birth'],
            'address_1' => $data['address'],
            'address_2' => null,

            'phone_cv' => $data['phone'],
            'phone_personal' => $data['phone'],
            'email_cv' => $data['email'],
            'email_personal' => $data['email'],

            'company_name' => $data['company_name'],
            'job_title' => $data['job_title'],
            'business_category' => $data['business_category'],

            'core_products' => $data['core_products'],
            'accompanying_services' => $data['accompanying_services'],
            'highlight_products' => $data['highlight_products'],

            'g_goals' => $data['g_goals'],
            'a_accomplishments' => $data['a_accomplishments'],
            'i_interests' => $data['i_interests'],
            'n_networks' => $data['n_networks'],
            's_skills' => $data['s_skills'],
        ];
    }

    protected function applyProfilePayload(GainsProfile $profile, array $data): void
    {
        foreach ($this->profilePayload($data) as $field => $value) {
            if ($this->shouldOverwriteProfileField($field, $value, $profile)) {
                $profile->{$field} = $value;
            }
        }
    }

    protected function shouldOverwriteProfileField(string $field, mixed $value, GainsProfile $profile): bool
    {
        if (! filled($value)) {
            return false;
        }

        if (in_array($field, ['email_cv', 'email_personal', 'phone_cv', 'phone_personal'], true)) {
            return true;
        }

        return blank($profile->{$field});
    }

    protected function findExistingProfile(array $data): ?GainsProfile
    {
        $profile = $this->findProfileByImportName($data);

        if ($profile) {
            return $profile;
        }

        if (filled($data['email']) || filled($data['phone'])) {
            return GainsProfile::query()
                ->where(function ($query) use ($data) {
                    if (filled($data['email'])) {
                        $query->where('email_cv', $data['email'])
                            ->orWhere('email_personal', $data['email']);
                    }

                    if (filled($data['phone'])) {
                        $query->orWhere('phone_cv', $data['phone'])
                            ->orWhere('phone_personal', $data['phone']);
                    }
                })
                ->first()
                ?? $this->findUserByImportContact($data)?->gainsProfile;
        }

        return null;
    }

    protected function findProfileByImportName(array $data): ?GainsProfile
    {
        if (filled($data['full_name'])) {
            return GainsProfile::query()
                ->whereRaw('LOWER(full_name) = LOWER(?)', [$data['full_name']])
                ->orWhere('slug', Str::slug($data['full_name']))
                ->orderBy('id')
                ->first();
        }

        return null;
    }

    protected function findOrCreateUser(array $data): ?User
    {
        return $this->syncUser($data);
    }

    protected function syncUser(array $data, ?GainsProfile $profile = null): ?User
    {
        $user = $this->findUserByImportContact($data) ?? $profile?->user;

        if (blank($data['email'])) {
            return $user;
        }

        if (! $user) {
            $user = User::create([
                'name' => $data['full_name'],
                'username' => $this->makeUniqueUsername($data['email'], $data['full_name']),
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make('kinhbac123'),
            ]);
        } else {
            $this->updateUserFromImport($user, $data);
        }

        if ($profile && $profile->user_id !== $user->id) {
            $profileUsingUser = GainsProfile::query()
                ->where('user_id', $user->id)
                ->whereKeyNot($profile->getKey())
                ->first();

            if ($profileUsingUser) {
                if (! $this->isSameImportedPerson($profileUsingUser, $data)) {
                    throw new \RuntimeException('Email/SĐT trong file đang thuộc user đã có hồ sơ GAINS khác: #' . $profileUsingUser->id);
                }

                $profileUsingUser->user_id = null;
                $profileUsingUser->save();
            }

            $profile->user_id = $user->id;
        }

        return $user;
    }

    protected function isSameImportedPerson(GainsProfile $profile, array $data): bool
    {
        if (blank($profile->full_name) || blank($data['full_name'])) {
            return false;
        }

        return $this->normalizePersonName($profile->full_name) === $this->normalizePersonName($data['full_name']);
    }

    protected function normalizePersonName(string $name): string
    {
        return Str::of($name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    protected function findUserByImportContact(array $data): ?User
    {
        if (blank($data['email']) && blank($data['phone'])) {
            return null;
        }

        return User::query()
            ->where(function ($query) use ($data) {
                if (filled($data['email'])) {
                    $query->whereRaw('LOWER(email) = ?', [strtolower($data['email'])]);
                }

                if (filled($data['phone'])) {
                    $query->orWhere('phone', $data['phone']);
                }
            })
            ->first();
    }

    protected function updateUserFromImport(User $user, array $data): void
    {
        if (filled($data['full_name'])) {
            $user->name = $data['full_name'];
        }

        if (blank($user->username)) {
            $user->username = $this->makeUniqueUsername($data['email'], $data['full_name']);
        }

        if (filled($data['email']) && strtolower((string) $user->email) !== $data['email']) {
            $emailOwner = User::query()
                ->whereRaw('LOWER(email) = ?', [strtolower($data['email'])])
                ->whereKeyNot($user->getKey())
                ->first();

            if ($emailOwner) {
                throw new \RuntimeException('Email trong file đã thuộc user khác: ' . $data['email']);
            }

            $user->email = $data['email'];
        }

        if (filled($data['phone']) && (string) $user->phone !== $data['phone']) {
            $phoneOwner = User::query()
                ->where('phone', $data['phone'])
                ->whereKeyNot($user->getKey())
                ->first();

            if ($phoneOwner) {
                throw new \RuntimeException('SĐT trong file đã thuộc user khác: ' . $data['phone']);
            }

            $user->phone = $data['phone'];
        }

        if ($user->isDirty()) {
            $user->save();
        }
    }

    protected function importDriveImages(GainsProfile $profile, array $urls, string $collectionName): void
    {
        $this->removeNonImageMedia($profile, $collectionName);

        foreach ($urls as $url) {
            if ($this->hasImportedImage($profile, $collectionName, $url)) {
                continue;
            }

            $downloadedPath = null;

            try {
                $downloadedPath = $this->downloadImageToTemporaryFile($url);

                $profile
                    ->addMedia($downloadedPath)
                    ->usingName($collectionName)
                    ->usingFileName($this->makeMediaFileName($url, $downloadedPath))
                    ->withCustomProperties([
                        'source_url' => $url,
                        'drive_file_id' => $this->googleDriveFileId($url),
                    ])
                    ->toMediaCollection($collectionName);

                $profile->unsetRelation('media');
            } catch (\Throwable $e) {
                // Không làm fail cả dòng chỉ vì 1 ảnh lỗi quyền Drive.
                $this->errors[] = [
                    'row' => null,
                    'message' => 'Không import được ảnh vào ' . $collectionName . ': ' . $url . ' - ' . $e->getMessage(),
                ];
            } finally {
                if ($downloadedPath && file_exists($downloadedPath)) {
                    @unlink($downloadedPath);
                }
            }
        }
    }

    protected function removeNonImageMedia(GainsProfile $profile, string $collectionName): void
    {
        $profile
            ->getMedia($collectionName)
            ->reject(fn ($media) => $this->isImageMedia($media))
            ->each(fn ($media) => $media->delete());

        $profile->unsetRelation('media');
    }

    protected function isImageMedia($media): bool
    {
        $extension = strtolower(pathinfo((string) $media->file_name, PATHINFO_EXTENSION));

        return str_starts_with((string) $media->mime_type, 'image/')
            && in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)
            && is_file($media->getPath());
    }

    protected function hasImportedImage(GainsProfile $profile, string $collectionName, string $url): bool
    {
        $driveFileId = $this->googleDriveFileId($url);

        return $profile
            ->getMedia($collectionName)
            ->contains(function ($media) use ($url, $driveFileId) {
                if ($media->getCustomProperty('source_url') === $url) {
                    return true;
                }

                return filled($driveFileId) && $media->getCustomProperty('drive_file_id') === $driveFileId;
            });
    }

    protected function downloadImageToTemporaryFile(string $url): string
    {
        $directUrl = $this->toGoogleDriveDownloadUrl($url) ?? $url;

        $response = Http::timeout(90)
            ->retry(2, 500)
            ->withOptions(['allow_redirects' => true])
            ->get($directUrl);

        if (! $response->successful()) {
            throw new \RuntimeException('Google Drive trả HTTP ' . $response->status() . '.');
        }

        $body = $response->body();

        if ($body === '') {
            throw new \RuntimeException('File tải về bị rỗng.');
        }

        if ($this->isGoogleSignInResponse($body)) {
            throw new \RuntimeException('Google Drive đang yêu cầu đăng nhập. Cần bật quyền xem công khai cho file/folder ảnh.');
        }

        if (str_contains(strtolower((string) $response->header('Content-Type')), 'text/html')) {
            throw new \RuntimeException('URL trả về HTML thay vì file ảnh. Kiểm tra lại quyền chia sẻ hoặc link ảnh.');
        }

        $extension = $this->imageExtensionFromContentType((string) $response->header('Content-Type'))
            ?? $this->imageExtensionFromUrl($url)
            ?? 'jpg';

        $path = 'imports/gains-profiles/downloaded-images/' . (string) Str::uuid() . '.' . $extension;

        Storage::disk('local')->put($path, $body);

        $absolutePath = Storage::disk('local')->path($path);

        if (! $this->isImageFile($absolutePath)) {
            Storage::disk('local')->delete($path);

            throw new \RuntimeException('File tải về không phải ảnh hoặc Drive chưa cấp quyền xem công khai.');
        }

        return $absolutePath;
    }

    protected function toGoogleDriveDownloadUrl(string $url): ?string
    {
        $fileId = $this->googleDriveFileId($url);

        if (! $fileId) {
            return null;
        }

        return 'https://drive.google.com/uc?export=download&id=' . $fileId;
    }

    protected function googleDriveFileId(string $url): ?string
    {
        $fileId = null;

        if (preg_match('/\/d\/([^\/]+)/', $url, $matches)) {
            $fileId = $matches[1];
        }

        if (! $fileId && preg_match('/id=([^&]+)/', $url, $matches)) {
            $fileId = $matches[1];
        }

        if (! $fileId) {
            return null;
        }

        return $fileId;
    }

    protected function makeMediaFileName(string $url, string $path): string
    {
        $driveFileId = $this->googleDriveFileId($url);
        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';

        return ($driveFileId ?: (string) Str::uuid()) . '.' . $extension;
    }

    protected function imageExtensionFromContentType(?string $contentType): ?string
    {
        $contentType = strtolower(trim((string) $contentType));

        return match (true) {
            str_contains($contentType, 'image/jpeg') => 'jpg',
            str_contains($contentType, 'image/png') => 'png',
            str_contains($contentType, 'image/webp') => 'webp',
            str_contains($contentType, 'image/gif') => 'gif',
            default => null,
        };
    }

    protected function imageExtensionFromUrl(string $url): ?string
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)
            ? ($extension === 'jpeg' ? 'jpg' : $extension)
            : null;
    }

    protected function isImageFile(string $path): bool
    {
        return is_file($path) && @getimagesize($path) !== false;
    }

    protected function isGoogleSignInResponse(string $body): bool
    {
        return str_contains($body, 'accounts.google.com')
            || str_contains($body, 'ServiceLogin')
            || str_contains($body, 'signin');
    }

    protected function extractUrls(string $value): array
    {
        if (blank($value)) {
            return [];
        }

        preg_match_all('/https?:\/\/[^\s,]+/', $value, $matches);

        return collect($matches[0] ?? [])
            ->map(fn ($url) => trim($url))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function pick(array $row, array $keys): ?string
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[$this->normalizeKey((string) $key)] = $value;
        }

        foreach ($keys as $key) {
            $value = Arr::get($normalized, $this->normalizeKey($key));

            if (filled($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    protected function normalizeKey(string $key): string
    {
        return Str::of($key)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        return preg_replace('/[^\d+]/', '', $phone);
    }

    protected function normalizeDate(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('d/m/Y');
            } catch (\Throwable) {
                return $value;
            }
        }

        return $value;
    }

    protected function makeUniqueSlug(?string $name): string
    {
        $base = Str::slug($name ?: 'gains-profile');
        $slug = $base;
        $i = 1;

        while (GainsProfile::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function makeUniqueUsername(?string $email, ?string $name): string
    {
        $base = $email
            ? Str::before($email, '@')
            : Str::slug($name ?: 'user');

        $username = $base;
        $i = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        return $username;
    }
}
