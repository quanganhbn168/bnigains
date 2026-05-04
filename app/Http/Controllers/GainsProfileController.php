<?php

namespace App\Http\Controllers;

use App\Models\GainsProfile;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GainsProfileController extends Controller
{
    public function show(string $slug)
    {
        $profile = GainsProfile::where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        $canonicalProfile = $this->canonicalProfileFor($profile);

        if ($canonicalProfile->isNot($profile)) {
            return redirect()->route('profile.show', ['slug' => $canonicalProfile->slug]);
        }

        return view('profile.show', $this->profileViewData($profile));
    }

    public function showByToken(string $qrToken)
    {
        $profile = GainsProfile::where('qr_token', $qrToken)
            ->where('is_public', true)
            ->firstOrFail();

        $canonicalProfile = $this->canonicalProfileFor($profile);

        if ($canonicalProfile->isNot($profile)) {
            return redirect()->route('profile.show', ['slug' => $canonicalProfile->slug]);
        }

        return view('profile.show', $this->profileViewData($profile));
    }

    public function downloadQrByToken(string $qrToken): Response
    {
        $profile = GainsProfile::where('qr_token', $qrToken)
            ->where('is_public', true)
            ->firstOrFail();

        $png = QrCode::format('png')
            ->size(600)
            ->margin(2)
            ->generate($profile->public_url);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr-' . $profile->slug . '.png"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function profileViewData(GainsProfile $profile): array
    {
        // Map field aliases so the old template keys read data from current DB schema.
        $profile->dob = $profile->dob ?? $profile->date_of_birth;
        $profile->pob = $profile->pob ?? $profile->place_of_birth;
        $profile->phone_cv_1 = $profile->phone_cv_1 ?? $profile->phone_cv;
        $profile->phone_cv_2 = $profile->phone_cv_2 ?? $profile->phone_personal;
        $profile->aspiration = $profile->aspiration ?? $profile->burning_desire;
        $profile->experience = $profile->experience ?? $profile->experience_years;
        $profile->degrees = $profile->degrees ?? $profile->qualifications;

        $profile->main_products = $this->normalizeList($profile->main_products ?? null);
        $profile->accompanying_services = $this->normalizeList($profile->accompanying_services ?? null);
        $profile->outstanding_products = $this->normalizeList($profile->outstanding_products ?? null);

        $profile->gains_goals = $this->normalizeList($profile->gains_goals ?? null);
        $profile->gains_accomplishments = $this->normalizeList($profile->gains_accomplishments ?? null);
        $profile->gains_interests = $this->normalizeList($profile->gains_interests ?? null);
        $profile->gains_networks = $this->normalizeList($profile->gains_networks ?? null);
        $profile->gains_skills = $this->normalizeList($profile->gains_skills ?? null);

        $profile->ideal_referrals = $this->normalizeList($profile->ideal_referrals ?? null);
        $profile->commitments = $this->normalizeList($profile->commitments ?? null);
        $profile->desired_introductions = $this->normalizeList($profile->desired_introductions ?? null);
        $profile->contact_sphere = $this->normalizeList($profile->contact_sphere ?? null);

        $personalPhotoUrls = $this->mediaUrls($profile, 'personal_photos');
        $businessPhotoUrls = $this->mediaUrls($profile, 'business_photos');
        $activityPhotoUrls = $this->mediaUrls($profile, 'activity_photos');
        $productGallery1Urls = $this->mediaUrls($profile, 'product_gallery_1');
        $productGallery2Urls = $this->mediaUrls($profile, 'product_gallery_2');
        $productGallery3Urls = $this->mediaUrls($profile, 'product_gallery_3');

        $bannerUrl = $profile->getFirstMediaUrl('banner');
        $avatarUrl = $profile->getFirstMediaUrl('avatar');
        $thanksBackgroundUrl = $profile->getFirstMediaUrl('footer') ?: $bannerUrl;

        $coreProductsHtml = $this->renderRichOrList($profile->core_products ?? null, $profile->main_products);
        $accompanyingServicesHtml = $this->renderRichOrList($profile->accompanying_services ?? null, $profile->accompanying_services);
        $highlightProductsHtml = $this->renderRichOrList($profile->highlight_products ?? null, $profile->outstanding_products);

        $gGoalsHtml = $this->renderRichOrList($profile->g_goals ?? null, $profile->gains_goals);
        $aAccomplishmentsHtml = $this->renderRichOrList($profile->a_accomplishments ?? null, $profile->gains_accomplishments);
        $iInterestsHtml = $this->renderRichOrList($profile->i_interests ?? null, $profile->gains_interests);
        $nNetworksHtml = $this->renderRichOrList($profile->n_networks ?? null, $profile->gains_networks);
        $sSkillsHtml = $this->renderRichOrList($profile->s_skills ?? null, $profile->gains_skills);

        $idealReferralHtml = $this->renderRichOrList($profile->ideal_referral ?? null, $profile->ideal_referrals, 'ul');
        $commitmentHtml = $this->renderRichOrList($profile->bni_commitment ?? null, $profile->commitments, 'ul');
        $wishesHtml = $this->renderRichOrList($profile->connection_wishes ?? null, $profile->desired_introductions, 'ul');
        $sphereHtml = $this->renderRichOrList($profile->connection_fields ?? null, $profile->contact_sphere, 'ul');
        $degreesHtml = is_string($profile->degrees)
            ? $profile->degrees
            : $this->renderRichOrList(null, (array) $profile->degrees);

        $hasBusinessInfo = filled($profile->company_name)
            || filled($profile->job_title)
            || filled($profile->business_category)
            || filled($profile->experience)
            || $this->hasContent($degreesHtml);

        return [
            'profile' => $profile,
            'pageTitle' => $profile->full_name . ' | BNI 1-2-1 Digital Profile',
            'bodyStyle' => $profile->theme_style,
            'profileFullNameUpper' => mb_strtoupper((string) $profile->full_name),
            'personalPhotoUrls' => $personalPhotoUrls,
            'businessPhotoUrls' => $businessPhotoUrls,
            'activityPhotoUrls' => $activityPhotoUrls,
            'productGallery1Urls' => $productGallery1Urls,
            'productGallery2Urls' => $productGallery2Urls,
            'productGallery3Urls' => $productGallery3Urls,
            'bannerUrl' => $bannerUrl,
            'avatarUrl' => $avatarUrl,
            'thanksBackgroundUrl' => $thanksBackgroundUrl,
            'coreProductsHtml' => $coreProductsHtml,
            'accompanyingServicesHtml' => $accompanyingServicesHtml,
            'highlightProductsHtml' => $highlightProductsHtml,
            'gGoalsHtml' => $gGoalsHtml,
            'aAccomplishmentsHtml' => $aAccomplishmentsHtml,
            'iInterestsHtml' => $iInterestsHtml,
            'nNetworksHtml' => $nNetworksHtml,
            'sSkillsHtml' => $sSkillsHtml,
            'idealReferralHtml' => $idealReferralHtml,
            'commitmentHtml' => $commitmentHtml,
            'wishesHtml' => $wishesHtml,
            'sphereHtml' => $sphereHtml,
            'degreesHtml' => $degreesHtml,
            'hasDegrees' => $this->hasContent($degreesHtml),
            'hasBusinessSection' => $hasBusinessInfo || ! empty($businessPhotoUrls),
            'hasProductsSection' => $this->hasContent($coreProductsHtml)
                || $this->hasContent($accompanyingServicesHtml)
                || $this->hasContent($highlightProductsHtml)
                || ! empty($productGallery1Urls)
                || ! empty($productGallery2Urls)
                || ! empty($productGallery3Urls),
            'hasGainsSection' => $this->hasContent($gGoalsHtml)
                || $this->hasContent($aAccomplishmentsHtml)
                || $this->hasContent($iInterestsHtml)
                || $this->hasContent($nNetworksHtml)
                || $this->hasContent($sSkillsHtml),
            'hasReferralSection' => $this->hasContent($idealReferralHtml)
                || $this->hasContent($commitmentHtml)
                || $this->hasContent($wishesHtml)
                || $this->hasContent($sphereHtml),
            'hasCommitmentSection' => $this->hasContent($commitmentHtml),
            'hasWishesSection' => $this->hasContent($wishesHtml),
            'hasPartnershipSection' => $this->hasContent($coreProductsHtml)
                || $this->hasContent($accompanyingServicesHtml)
                || $this->hasContent($highlightProductsHtml),
            'hasSphereSection' => $this->hasContent($sphereHtml),
        ];
    }

    private function normalizeList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        if (is_null($value)) {
            return [];
        }

        if (is_string($value)) {
            $text = trim($value);

            if ($text === '') {
                return [];
            }

            $text = str_ireplace(['<br>', '<br/>', '<br />', '</p><p>', '</li><li>', '</ul><ul>', '&nbsp;'], ["\n", "\n", "\n", "\n", "\n", "\n", ' '], $text);
            $text = strip_tags($text);

            $parts = preg_split("/\r\n|\r|\n|•|\t|\\s{2,}/", $text) ?: [];

            return array_values(array_filter(array_map('trim', $parts), fn ($v) => $v !== ''));
        }

        if (is_object($value)) {
            return (array) $value;
        }

        return [];
    }

    private function renderRichOrList(mixed $richValue, array $listValue, string $listType = 'p'): string
    {
        $rich = is_string($richValue) ? trim($richValue) : '';

        if ($rich !== '') {
            return $rich;
        }

        if (empty($listValue)) {
            return '';
        }

        if ($listType === 'ul') {
            $items = collect($listValue)
                ->map(fn ($value) => '<li class="leading-tight">' . e($value) . '</li>')
                ->implode('');

            return '<ul class="list-disc pl-2 space-y-2.5 marker:text-black">' . $items . '</ul>';
        }

        return collect($listValue)
            ->map(fn ($value) => '<p class="leading-tight">' . e($value) . '</p>')
            ->implode('');
    }

    private function hasContent(?string $html): bool
    {
        if (! is_string($html)) {
            return false;
        }

        return trim(strip_tags($html)) !== '';
    }

    private function mediaUrls(GainsProfile $profile, string $collection): array
    {
        return $profile->getMedia($collection)
            ->filter(fn ($media) => $this->isImageMedia($media))
            ->map(fn ($media) => $media->getUrl())
            ->values()
            ->all();
    }

    private function isImageMedia($media): bool
    {
        $extension = strtolower(pathinfo((string) $media->file_name, PATHINFO_EXTENSION));

        return str_starts_with((string) $media->mime_type, 'image/')
            && in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)
            && is_file($media->getPath());
    }

    private function canonicalProfileFor(GainsProfile $profile): GainsProfile
    {
        if (filled($profile->user_id)) {
            return $profile;
        }

        $candidates = GainsProfile::query()
            ->whereKeyNot($profile->getKey())
            ->whereNotNull('user_id')
            ->where(function ($query) use ($profile) {
                if (filled($profile->email_cv)) {
                    $query->orWhere('email_cv', $profile->email_cv)
                        ->orWhere('email_personal', $profile->email_cv);
                }

                if (filled($profile->email_personal)) {
                    $query->orWhere('email_cv', $profile->email_personal)
                        ->orWhere('email_personal', $profile->email_personal);
                }

                if (filled($profile->phone_cv)) {
                    $query->orWhere('phone_cv', $profile->phone_cv)
                        ->orWhere('phone_personal', $profile->phone_cv);
                }

                if (filled($profile->phone_personal)) {
                    $query->orWhere('phone_cv', $profile->phone_personal)
                        ->orWhere('phone_personal', $profile->phone_personal);
                }

                $query->orWhere('slug', preg_replace('/-\d+$/', '', (string) $profile->slug));
            })
            ->orderBy('id')
            ->get();

        return $candidates
            ->first(fn (GainsProfile $candidate) => $this->sameProfilePerson($candidate, $profile))
            ?? $profile;
    }

    private function sameProfilePerson(GainsProfile $a, GainsProfile $b): bool
    {
        if (filled($a->email_cv) && filled($b->email_cv) && $a->email_cv === $b->email_cv) {
            return true;
        }

        if (filled($a->phone_cv) && filled($b->phone_cv) && $a->phone_cv === $b->phone_cv) {
            return true;
        }

        if (blank($a->full_name) || blank($b->full_name)) {
            return false;
        }

        return $this->normalizePersonName($a->full_name) === $this->normalizePersonName($b->full_name);
    }

    private function normalizePersonName(string $name): string
    {
        return \Illuminate\Support\Str::of($name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }
}
