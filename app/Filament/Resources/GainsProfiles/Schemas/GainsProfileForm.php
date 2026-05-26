<?php

namespace App\Filament\Resources\GainsProfiles\Schemas;

use App\Models\GainsProfile;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class GainsProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Hồ Sơ 1-2-1')
                    ->tabs([
                        Tab::make('Cá nhân & Gia đình')
                            ->schema([
                                TextInput::make('full_name')
                                    ->label('Họ và tên (hiển thị trên thẻ)')
                                    ->required()
                                    ->columnSpanFull(),

                                TextInput::make('education')
                                    ->label('Học vấn')
                                    ->placeholder('VD: Đại học Kinh tế Quốc dân'),

                                TextInput::make('bni_position')
                                    ->label('Chức vụ BNI')
                                    ->placeholder('VD: Chủ tịch NK20'),

                                TextInput::make('chapter_name')
                                    ->label('Tên Chapter')
                                    ->default('BNI KINHBAC CHAPTER')
                                    ->placeholder('VD: BNI Power Chapter - HN2'),

                                Toggle::make('is_public')
                                    ->label('Cho phép hiển thị public')
                                    ->default(true)
                                    ->inline(false),

                                Select::make('select_banner_from_personal_photos')
                                    ->label('Chọn ảnh Banner từ Album cá nhân')
                                    ->placeholder('Bấm để chọn ảnh có sẵn trong Album Ảnh cá nhân')
                                    ->options(fn (?GainsProfile $record): array => static::getPersonalPhotoOptions($record))
                                    ->allowHtml()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->dehydrated(false)
                                    ->afterStateUpdated(function (Set $set, mixed $state, ?GainsProfile $record): void {
                                        static::copyPersonalPhotoToCollection(
                                            state: $state,
                                            record: $record,
                                            set: $set,
                                            targetCollection: 'banner',
                                            targetStatePath: 'banner',
                                            selectStatePath: 'select_banner_from_personal_photos',
                                            successMessage: 'Đã đưa ảnh đã chọn vào Banner.'
                                        );
                                    })
                                    ->partiallyRenderComponentsAfterStateUpdated(['banner'])
                                    ->helperText('Chọn ảnh từ Album Ảnh cá nhân. Hệ thống sẽ copy ảnh này vào đúng trường Banner, không lưu link.')
                                    ->columnSpanFull(),

                                Select::make('select_avatar_from_personal_photos')
                                    ->label('Chọn ảnh Avatar từ Album cá nhân')
                                    ->placeholder('Bấm để chọn ảnh có sẵn trong Album Ảnh cá nhân')
                                    ->options(fn (?GainsProfile $record): array => static::getPersonalPhotoOptions($record))
                                    ->allowHtml()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->dehydrated(false)
                                    ->afterStateUpdated(function (Set $set, mixed $state, ?GainsProfile $record): void {
                                        static::copyPersonalPhotoToCollection(
                                            state: $state,
                                            record: $record,
                                            set: $set,
                                            targetCollection: 'avatar',
                                            targetStatePath: 'avatar',
                                            selectStatePath: 'select_avatar_from_personal_photos',
                                            successMessage: 'Đã đưa ảnh đã chọn vào Avatar.'
                                        );
                                    })
                                    ->partiallyRenderComponentsAfterStateUpdated(['avatar'])
                                    ->helperText('Chọn ảnh từ Album Ảnh cá nhân. Hệ thống sẽ copy ảnh này vào đúng trường Avatar, không lưu link.')
                                    ->columnSpanFull(),

                                SpatieMediaLibraryFileUpload::make('banner')
                                    ->collection('banner')
                                    ->label('Ảnh Banner (Bìa trên cùng)')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '9:16',
                                    ])
                                    ->helperText('Có thể upload trực tiếp, hoặc chọn từ Album Ảnh cá nhân ở ô bên trên.')
                                    ->maxFiles(1)
                                    ->panelLayout('grid')
                                    ->columnSpanFull(),

                                SpatieMediaLibraryFileUpload::make('avatar')
                                    ->collection('avatar')
                                    ->label('Ảnh chân dung')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '9:16',
                                    ])
                                    ->helperText('Có thể upload trực tiếp, hoặc chọn từ Album Ảnh cá nhân ở ô bên trên.')
                                    ->maxFiles(1)
                                    ->panelLayout('grid')
                                    ->columnSpanFull(),

                                SpatieMediaLibraryFileUpload::make('personal_photos')
                                    ->collection('personal_photos')
                                    ->label('Album Ảnh cá nhân')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),

                                TextInput::make('user_email')
                                    ->label('Email đăng nhập')
                                    ->email()
                                    ->required()
                                    ->helperText('Hệ thống sẽ tự tạo tài khoản với mật khẩu mặc định: kinhbac123'),

                                TextInput::make('phone_cv')
                                    ->tel()
                                    ->label('SĐT công việc'),

                                TextInput::make('phone_personal')
                                    ->tel()
                                    ->label('SĐT cá nhân'),

                                TextInput::make('email_cv')
                                    ->email()
                                    ->label('Email công việc'),

                                TextInput::make('email_personal')
                                    ->email()
                                    ->label('Email cá nhân'),

                                TextInput::make('date_of_birth')
                                    ->label('Ngày sinh'),

                                TextInput::make('address')
                                    ->label('Địa chỉ')
                                    ->columnSpanFull(),

                                RichEditor::make('family_info')
                                    ->label('Thông tin gia đình')
                                    ->columnSpanFull(),

                                RichEditor::make('burning_desire')
                                    ->label('Khát vọng cháy bỏng')
                                    ->columnSpanFull(),

                                RichEditor::make('unknown_fact')
                                    ->label('Điều chưa ai biết về tôi')
                                    ->columnSpanFull(),

                                RichEditor::make('success_key')
                                    ->label('Chìa khóa thành công')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Doanh nghiệp & Sản phẩm')
                            ->schema([
                                TextInput::make('company_name')
                                    ->label('Tên công ty')
                                    ->columnSpanFull(),

                                TextInput::make('job_title')
                                    ->label('Chức danh'),

                                TextInput::make('business_category')
                                    ->label('Lĩnh vực'),

                                TextInput::make('experience_years')
                                    ->label('Kinh nghiệm'),

                                RichEditor::make('qualifications')
                                    ->label('Bằng cấp / Chứng chỉ')
                                    ->columnSpanFull(),

                                SpatieMediaLibraryFileUpload::make('business_photos')
                                    ->collection('business_photos')
                                    ->label('Album Ảnh Doanh nghiệp')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),

                                RichEditor::make('core_products')
                                    ->label('Sản phẩm chính')
                                    ->columnSpanFull(),

                                RichEditor::make('accompanying_services')
                                    ->label('Dịch vụ đi kèm')
                                    ->columnSpanFull(),

                                RichEditor::make('highlight_products')
                                    ->label('Sản phẩm nổi bật')
                                    ->columnSpanFull(),

                                SpatieMediaLibraryFileUpload::make('product_gallery_1')
                                    ->collection('product_gallery_1')
                                    ->label('Album/Sản phẩm 1')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),

                                SpatieMediaLibraryFileUpload::make('product_gallery_2')
                                    ->collection('product_gallery_2')
                                    ->label('Album/Sản phẩm 2')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),

                                SpatieMediaLibraryFileUpload::make('product_gallery_3')
                                    ->collection('product_gallery_3')
                                    ->label('Album/Sản phẩm 3')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Bảng GAINS')
                            ->schema([
                                RichEditor::make('g_goals')
                                    ->label('Goals – Mục tiêu')
                                    ->columnSpanFull(),

                                RichEditor::make('a_accomplishments')
                                    ->label('Accomplishments – Thành tựu')
                                    ->columnSpanFull(),

                                RichEditor::make('i_interests')
                                    ->label('Interests – Sở thích')
                                    ->columnSpanFull(),

                                RichEditor::make('n_networks')
                                    ->label('Networks – Mạng lưới kết nối')
                                    ->columnSpanFull(),

                                RichEditor::make('s_skills')
                                    ->label('Skills – Kỹ năng')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Cẩm nang Referral')
                            ->schema([
                                RichEditor::make('ideal_referral')
                                    ->label('Referral lý tưởng')
                                    ->columnSpanFull(),

                                RichEditor::make('connection_wishes')
                                    ->label('Mong muốn được giới thiệu')
                                    ->columnSpanFull(),

                                RichEditor::make('bni_commitment')
                                    ->label('Cam kết trong BNI')
                                    ->columnSpanFull(),

                                SpatieMediaLibraryFileUpload::make('activity_photos')
                                    ->collection('activity_photos')
                                    ->label('Ảnh hoạt động kết nối')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),

                                RichEditor::make('product_description')
                                    ->label('1. Mô tả sản phẩm/dịch vụ')
                                    ->columnSpanFull(),

                                RichEditor::make('competitive_advantage')
                                    ->label('2. Điểm khác biệt')
                                    ->columnSpanFull(),

                                RichEditor::make('target_market')
                                    ->label('3. Khách hàng/Thị trường mục tiêu')
                                    ->columnSpanFull(),

                                RichEditor::make('connection_fields')
                                    ->label('4. Ngành nghề kết hợp')
                                    ->columnSpanFull(),

                                RichEditor::make('conversation_starters')
                                    ->label('5. Khơi gợi nhu cầu')
                                    ->columnSpanFull(),

                                RichEditor::make('trigger_phrases')
                                    ->label('6. Lời giới thiệu tốt là gì?')
                                    ->columnSpanFull(),

                                RichEditor::make('good_referral')
                                    ->label('7. Referral tốt là ai?')
                                    ->columnSpanFull(),

                                RichEditor::make('bad_referral')
                                    ->label('8. Referral không phù hợp')
                                    ->columnSpanFull(),

                                RichEditor::make('misconceptions')
                                    ->label('9. Quan niệm sai/Xử lý từ chối')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function getPersonalPhotoOptions(?GainsProfile $record): array
    {
        if (!$record) {
            return [];
        }

        return $record
            ->getMedia('personal_photos')
            ->mapWithKeys(function (Media $media, int $index): array {
                $number = $index + 1;
                $url = $media->getUrl();
                $fileName = $media->file_name;
                $createdAt = $media->created_at?->format('d/m/Y H:i');

                $label = '<div style="display:flex;align-items:center;gap:10px;">'
                    . '<img src="' . e($url) . '" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #ddd;" />'
                    . '<div style="line-height:1.35;">'
                        . '<div><strong>Ảnh #' . e((string) $number) . '</strong></div>'
                        . '<div style="font-size:12px;color:#666;">' . e($fileName) . '</div>'
                        . '<div style="font-size:12px;color:#999;">' . e((string) $createdAt) . '</div>'
                    . '</div>'
                . '</div>';

                return [
                    $media->id => $label,
                ];
            })
            ->toArray();
    }

    protected static function copyPersonalPhotoToCollection(
        mixed $state,
        ?GainsProfile $record,
        Set $set,
        string $targetCollection,
        string $targetStatePath,
        string $selectStatePath,
        string $successMessage,
    ): void {
        if (!$state || !$record) {
            return;
        }

        $media = Media::query()->find($state);

        if (!$media) {
            Notification::make()
                ->title('Không tìm thấy ảnh đã chọn.')
                ->danger()
                ->send();

            return;
        }

        if (
            $media->model_type !== GainsProfile::class
            || (int) $media->model_id !== (int) $record->id
            || $media->collection_name !== 'personal_photos'
        ) {
            Notification::make()
                ->title('Ảnh này không thuộc Album Ảnh cá nhân của hồ sơ hiện tại.')
                ->danger()
                ->send();

            return;
        }

        try {
            $copiedMedia = $media->copy(
                $record,
                $targetCollection,
                $media->disk
            );
        } catch (Throwable $exception) {
            Log::error('Không copy được ảnh Album cá nhân sang collection đích.', [
                'profile_id' => $record->id,
                'source_media_id' => $media->id,
                'target_collection' => $targetCollection,
                'message' => $exception->getMessage(),
            ]);

            Notification::make()
                ->title('Không copy được ảnh đã chọn.')
                ->body('File ảnh gốc có thể không còn tồn tại hoặc storage đang lỗi. Ảnh cũ vẫn được giữ nguyên.')
                ->danger()
                ->send();

            return;
        }

        $uuid = $copiedMedia->getAttributeValue('uuid');

        if (blank($uuid)) {
            Log::error('Ảnh đã copy nhưng media mới không có UUID.', [
                'profile_id' => $record->id,
                'source_media_id' => $media->id,
                'copied_media_id' => $copiedMedia->id,
                'target_collection' => $targetCollection,
            ]);

            $copiedMedia->delete();

            Notification::make()
                ->title('Không cập nhật được ảnh đã chọn.')
                ->body('Media mới không có mã UUID hợp lệ. Ảnh cũ vẫn được giữ nguyên.')
                ->danger()
                ->send();

            return;
        }

        try {
            $record->clearMediaCollectionExcept($targetCollection, $copiedMedia);
        } catch (Throwable $exception) {
            Log::warning('Đã copy ảnh mới nhưng chưa dọn được ảnh cũ trong collection đích.', [
                'profile_id' => $record->id,
                'copied_media_id' => $copiedMedia->id,
                'target_collection' => $targetCollection,
                'message' => $exception->getMessage(),
            ]);
        }

        $record->unsetRelation('media');
        $record->load('media');

        $set($targetStatePath, [$uuid => $uuid]);
        $set($selectStatePath, null);

        Notification::make()
            ->title($successMessage)
            ->success()
            ->send();
    }
}
