@extends('layouts.profile')

@section('title', $profile->full_name . ' | BNI 1-2-1 Digital Profile')

@section('body_style', $profile->theme_style)

@php
    $normalizeList = function ($value): array {
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

            // Convert common HTML line breaks to new lines, then split.
            $text = str_ireplace(['<br>', '<br/>', '<br />', '</p><p>', '</li><li>', '</ul><ul>', '&nbsp;'], ["\n", "\n", "\n", "\n", "\n", "\n", ' '], $text);
            $text = strip_tags($text);

            $parts = preg_split("/\r\n|\r|\n|•|\t|\\s{2,}/", $text) ?: [];

            return array_values(array_filter(array_map('trim', $parts), fn ($v) => $v !== ''));
        }

        if (is_object($value)) {
            return (array) $value;
        }

        return [];
    };

    $renderRichOrList = function ($richValue, array $listValue, string $listType = 'p'): string {
        $rich = is_string($richValue) ? trim($richValue) : '';

        if ($rich !== '') {
            return $rich;
        }

        if (empty($listValue)) {
            return '';
        }

        if ($listType === 'ul') {
            $items = collect($listValue)
                ->map(fn ($v) => '<li class="leading-tight">' . e($v) . '</li>')
                ->implode('');

            return '<ul class="list-disc pl-2 space-y-2.5 marker:text-black">' . $items . '</ul>';
        }

        $items = collect($listValue)
            ->map(fn ($v) => '<p class="leading-tight">' . e($v) . '</p>')
            ->implode('');

        return $items;
    };

    $hasContent = function (?string $html): bool {
        if (!is_string($html)) {
            return false;
        }

        return trim(strip_tags($html)) !== '';
    };

    // Map field aliases so the old template keys read data from current DB schema.
    $profile->dob = $profile->dob ?? $profile->date_of_birth;
    $profile->pob = $profile->pob ?? $profile->place_of_birth;
    $profile->phone_cv_1 = $profile->phone_cv_1 ?? $profile->phone_cv;
    $profile->phone_cv_2 = $profile->phone_cv_2 ?? $profile->phone_personal;
    $profile->aspiration = $profile->aspiration ?? $profile->burning_desire;
    $profile->experience = $profile->experience ?? $profile->experience_years;
    $profile->degrees = $profile->degrees ?? $profile->qualifications;

    // Normalize list-like fields to arrays (handles string input from RichEditor/text)
    $profile->main_products = $normalizeList($profile->main_products ?? null);
    $profile->accompanying_services = $normalizeList($profile->accompanying_services ?? null);
    $profile->outstanding_products = $normalizeList($profile->outstanding_products ?? null);

    $profile->gains_goals = $normalizeList($profile->gains_goals ?? null);
    $profile->gains_accomplishments = $normalizeList($profile->gains_accomplishments ?? null);
    $profile->gains_interests = $normalizeList($profile->gains_interests ?? null);
    $profile->gains_networks = $normalizeList($profile->gains_networks ?? null);
    $profile->gains_skills = $normalizeList($profile->gains_skills ?? null);

    $profile->ideal_referrals = $normalizeList($profile->ideal_referrals ?? null);
    $profile->commitments = $normalizeList($profile->commitments ?? null);
    $profile->desired_introductions = $normalizeList($profile->desired_introductions ?? null);
    $profile->contact_sphere = $normalizeList($profile->contact_sphere ?? null);

    $personalPhotoUrls = $profile->getMedia('personal_photos')->map(fn ($m) => $m->getUrl())->all();
    $businessPhotoUrls = $profile->getMedia('business_photos')->map(fn ($m) => $m->getUrl())->all();
    $activityPhotoUrls = $profile->getMedia('activity_photos')->map(fn ($m) => $m->getUrl())->all();
    $productGallery1Urls = $profile->getMedia('product_gallery_1')->map(fn ($m) => $m->getUrl())->all();
    $productGallery2Urls = $profile->getMedia('product_gallery_2')->map(fn ($m) => $m->getUrl())->all();
    $productGallery3Urls = $profile->getMedia('product_gallery_3')->map(fn ($m) => $m->getUrl())->all();

    $bannerUrl = $profile->getFirstMediaUrl('banner');
    $avatarUrl = $profile->getFirstMediaUrl('avatar');
    $thanksBackgroundUrl = $profile->getFirstMediaUrl('footer') ?: $bannerUrl;

    $coreProductsHtml = $renderRichOrList($profile->core_products ?? null, $profile->main_products);
    $accompanyingServicesHtml = $renderRichOrList($profile->accompanying_services ?? null, $profile->accompanying_services);
    $highlightProductsHtml = $renderRichOrList($profile->highlight_products ?? null, $profile->outstanding_products);

    $gGoalsHtml = $renderRichOrList($profile->g_goals ?? null, $profile->gains_goals);
    $aAccomplishmentsHtml = $renderRichOrList($profile->a_accomplishments ?? null, $profile->gains_accomplishments);
    $iInterestsHtml = $renderRichOrList($profile->i_interests ?? null, $profile->gains_interests);
    $nNetworksHtml = $renderRichOrList($profile->n_networks ?? null, $profile->gains_networks);
    $sSkillsHtml = $renderRichOrList($profile->s_skills ?? null, $profile->gains_skills);

    $idealReferralHtml = $renderRichOrList($profile->ideal_referral ?? null, $profile->ideal_referrals, 'ul');
    $commitmentHtml = $renderRichOrList($profile->bni_commitment ?? null, $profile->commitments, 'ul');
    $wishesHtml = $renderRichOrList($profile->connection_wishes ?? null, $profile->desired_introductions, 'ul');
    $sphereHtml = $renderRichOrList($profile->connection_fields ?? null, $profile->contact_sphere, 'ul');
    $familyInfoHtml = is_string($profile->family_info ?? null) ? $profile->family_info : '';

    $hasBusinessInfo = filled($profile->company_name)
        || filled($profile->job_title)
        || filled($profile->business_category)
        || filled($profile->experience)
        || $hasContent(is_string($profile->degrees) ? $profile->degrees : null);
    $hasBusinessSection = $hasBusinessInfo || !empty($businessPhotoUrls);

    $hasProductsSection = $hasContent($coreProductsHtml)
        || $hasContent($accompanyingServicesHtml)
        || $hasContent($highlightProductsHtml)
        || !empty($productGallery1Urls)
        || !empty($productGallery2Urls)
        || !empty($productGallery3Urls);

    $hasGainsSection = $hasContent($gGoalsHtml)
        || $hasContent($aAccomplishmentsHtml)
        || $hasContent($iInterestsHtml)
        || $hasContent($nNetworksHtml)
        || $hasContent($sSkillsHtml);

    $hasReferralSection = $hasContent($idealReferralHtml)
        || $hasContent($commitmentHtml)
        || $hasContent($wishesHtml)
        || $hasContent($sphereHtml);
@endphp

@section('content')
    <!-- BANNER & NAME BADGE -->
    <section id="top" class="relative w-full z-10">
        <!-- Banner Image -->
        <div class="w-full relative shadow-sm">
            @if($bannerUrl)
                <div class="w-full aspect-[9/16] overflow-hidden">
                    <img src="{{ $bannerUrl }}" class="w-full h-full object-cover block" alt="Banner">
                </div>
            @endif

            <!-- Subtle gradient to blend portrait at bottom -->
            <div
                class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-gray-900/60 to-transparent pointer-events-none hidden md:block">
            </div>

            <!-- Gold Name Badge Wrapper positioned at top 70% -->
            <div class="absolute inset-x-0 flex justify-center z-20 px-4" style="top: 70%;">
                <!-- Outer Gold Edge -->
                <div
                    class="w-full max-w-[340px] rounded-[1.5rem] p-[2.5px] bg-gradient-to-r from-[#d4af37] via-[#fff5c3] to-[#c5a059] shadow-2xl overflow-hidden group cursor-default">
                    <!-- White Gap -->
                    <div class="w-full h-full rounded-[1.35rem] p-[2.5px] bg-[#fdfdfc]">
                        <!-- Inner Thin Gold & Cream Background -->
                        <div
                            class="w-full h-full rounded-[1.2rem] border border-[#c5a059]/40 bg-gradient-to-b from-[#ffffff] to-[#fcf7ec] px-4 py-5 text-center flex flex-col items-center justify-center relative overflow-hidden shadow-inner">

                            <!-- Shine effect overlay -->
                            <div
                                class="absolute top-0 bottom-0 left-0 w-[200%] transform -translate-x-[150%] skew-x-[-20deg] bg-gradient-to-r from-transparent via-white/50 to-transparent group-hover:translate-x-[50%] transition-transform duration-[1500ms] ease-in-out pointer-events-none z-0">
                            </div>

                            <h1
                                class="text-[20px] font-black text-gray-900 tracking-wider uppercase leading-snug drop-shadow-sm relative z-10 mt-1">
                                {{ $profile->full_name }}
                            </h1>
                            <p class="text-[11px] font-bold text-[#CC0000] mt-1.5 uppercase tracking-[0.2em] relative z-10">
                                {{ $profile->bni_position }}</p>

                            <div
                                class="w-16 h-[2px] bg-gradient-to-r from-transparent via-[#d4af37] to-transparent my-3 relative z-10">
                            </div>

                            <p class="text-[13px] text-gray-800 font-extrabold uppercase tracking-widest relative z-10">
                                {{ $profile->chapter_name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MEMBER INFORMATION SECTION -->
    <section class="relative w-full py-12 bg-gray-50 bg-cover bg-center"
        style="background-image: url('{{ asset('images/bg-pattern.jpg') }}');">
        <!-- Overlay to slightly soften the pattern if needed -->
        <div class="absolute inset-0 bg-white/40"></div>

        <div class="relative z-10 px-5">
            <!-- Section Header -->
            <div class="text-center mb-10">
                <h2
                    class="text-[26px] md:text-3xl font-philosopher font-bold text-[#D31215] uppercase tracking-wide drop-shadow-sm">
                    THÔNG TIN THÀNH VIÊN
                </h2>
                <!-- Decorative Image Divider -->
                <div class="flex justify-center mt-3 opacity-90 mix-blend-multiply">
                    <img src="{{ asset('images/divine.png') }}" class="w-[280px] object-contain" alt="divider">
                </div>
            </div>

            <!-- CONTINUOUS INFO CARD -->
            <div
                class="relative w-full max-w-[440px] mx-auto bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] pt-12 pb-12 border border-white/60 mb-8 overflow-hidden">

                <!-- 1. THÔNG TIN CÁ NHÂN -->
                <div id="thong-tin-ca-nhan" class="flex justify-center relative z-10 mb-6">
                    <x-profile.badge>THÔNG TIN CÁ NHÂN</x-profile.badge>
                </div>
                @if($avatarUrl)
                    <div class="px-6 mb-6">
                        <div class="w-full aspect-[9/16] overflow-hidden rounded-xl shadow-sm">
                            <img src="{{ $avatarUrl }}" class="w-full h-full object-cover" alt="Ảnh chân dung">
                        </div>
                    </div>
                @endif
                <!-- Content Personal -->
                <div class="space-y-3.5 text-[18px] text-gray-900 leading-relaxed font-tinos px-6">
                    <p><span class="font-bold mr-1">Họ tên:</span> {{ mb_strtoupper($profile->full_name) }}</p>
                    <p><span class="font-bold mr-1">Ngày sinh:</span> {{ $profile->dob }}</p>


                    <p><span class="font-bold mr-1">Địa chỉ hiện tại:</span> <span
                            class="leading-tight inline-block">{{ $profile->address_1 }}</span></p>

                    @if($profile->address_2)
                        <p><span class="font-bold mr-1">Địa chỉ hiện tại 2:</span> <span
                                class="leading-tight inline-block">{{ $profile->address_2 }}</span></p>
                    @endif

                    <div class="pt-1">
                        <p class="font-bold">Điện thoại:</p>
                        <div class="mt-1.5 ml-4 space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-sm bg-gray-500"></div>
                                <span class="tracking-wide">{{ $profile->phone_personal }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. ẢNH DỌC/NGANG CHIA SECTION -->
                <div id="album-ca-nhan" class="w-full mt-10 mb-6">
                    @if(!empty($personalPhotoUrls))
                        <swiper-container pagination="true" pagination-clickable="true" loop="true" autoplay-delay="2500" class="w-full">
                            @foreach($personalPhotoUrls as $idx => $photoUrl)
                                <swiper-slide>
                                    <img src="{{ $photoUrl }}" class="w-full aspect-[4/3] object-cover shadow-inner" alt="Album cá nhân {{ $idx + 1 }}">
                                </swiper-slide>
                            @endforeach
                        </swiper-container>
                    @endif
                </div>



                <!-- 4. THÔNG TIN KHÁC -->
                {{-- <div class="flex justify-center relative z-10 mt-12 mb-6">
                    <x-profile.badge>THÔNG TIN KHÁC</x-profile.badge>
                </div> --}}

                {{-- <div class="space-y-4 text-[18px] text-gray-900 leading-relaxed font-tinos px-6">
                    <div>
                        <p class="font-bold mb-1.5">Khát vọng cháy bỏng của tôi là:</p>
                        <div class="pl-0">{!! $profile->aspiration !!}</div>
                    </div>
                    @if($profile->unknown_fact)
                        <div>
                            <p class="font-bold mb-1.5">Những điều mà có lẽ chưa ai biết về tôi:</p>
                            <div class="pl-0">{!! $profile->unknown_fact !!}</div>
                        </div>
                    @endif
                </div> --}}

            </div>

            @if($hasBusinessSection)
                @if(!empty($businessPhotoUrls))
                    <!-- ALBUM DOANH NGHIỆP -->
                    <div class="w-full max-w-[440px] mx-auto mb-8">
                        <div class="flex justify-center mb-4">
                            <x-profile.badge>ALBUM DOANH NGHIỆP</x-profile.badge>
                        </div>

                        <swiper-container pagination="true" pagination-clickable="true" loop="true" autoplay-delay="2500" class="w-full">
                            @foreach($businessPhotoUrls as $idx => $photoUrl)
                                <swiper-slide>
                                    <img src="{{ $photoUrl }}" class="w-full aspect-[4/3] object-cover" alt="Ảnh doanh nghiệp {{ $idx + 1 }}">
                                </swiper-slide>
                            @endforeach
                        </swiper-container>
                    </div>
                @endif

                <!-- THÔNG TIN DOANH NGHIỆP CARD -->
                <div id="thong-tin-doanh-nghiep" class="relative w-full max-w-[440px] mx-auto bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] pt-10 pb-10 border border-white/60 mb-8 overflow-hidden">

                <div class="flex justify-center relative z-10 mb-6">
                    <x-profile.badge>THÔNG TIN DOANH NGHIỆP</x-profile.badge>
                </div>

                <!-- Content Business -->
                <div class="space-y-3.5 text-[18px] text-gray-900 leading-relaxed font-tinos px-6">
                    @if($profile->company_name)<p><span class="font-bold mr-1">Tên công ty:</span> <span class="leading-tight inline-block">{{ $profile->company_name }}</span></p>@endif
                    @if($profile->job_title)<p><span class="font-bold mr-1">Chức danh:</span> {{ $profile->job_title }}</p>@endif
                    @if($profile->business_category)<p><span class="font-bold mr-1">Lĩnh vực:</span> <span class="leading-tight inline-block">{{ $profile->business_category }}</span></p>@endif

                    <div class="pt-1">
                        <p class="font-bold mb-1">Kinh nghiệm:</p>
                        <p class="pl-0">{{ $profile->experience }}</p>
                    </div>

                    @if(!empty($profile->degrees))
                        <div class="pt-1">
                            <p class="font-bold mb-1.5">Bằng cấp:</p>
                            <div class="space-y-1 pl-0">
                                {!! is_string($profile->degrees) ? $profile->degrees : $renderRichOrList(null, (array) $profile->degrees) !!}
                            </div>
                        </div>
                    @endif
                </div>
                </div>

                <!-- SWIPER SLIDER ẢNH ĐƠN -->
                @if(!empty($activityPhotoUrls))
                    <div class="w-full max-w-[440px] mx-auto mb-10 overflow-hidden rounded-xl shadow-md border border-gray-100 relative">
                        <swiper-container pagination="true" pagination-clickable="true" loop="true" autoplay-delay="3000" class="w-full">
                            @foreach($activityPhotoUrls as $idx => $photoUrl)
                                <swiper-slide>
                                    <img src="{{ $photoUrl }}" class="w-full aspect-[4/3] object-cover" alt="Ảnh hoạt động {{ $idx + 1 }}">
                                </swiper-slide>
                            @endforeach
                        </swiper-container>
                    </div>
                @endif
            @endif

        </div>
    </section>

    <!-- TOP PRODUCTS SECTION -->
    @if($hasProductsSection)
    <section id="san-pham" class="relative w-full py-12 bg-gray-50 bg-cover bg-center" style="background-image: url('{{ asset('images/bg-pattern.jpg') }}');">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-white/40"></div>

        <div class="relative z-10 px-5">
            <!-- Section Header -->
            <div class="text-center mb-10">
                <h2 class="text-[26px] md:text-3xl font-philosopher font-bold text-[#D31215] uppercase tracking-wide drop-shadow-sm">
                    SẢN PHẨM HÀNG ĐẦU
                </h2>
                <!-- Decorative Image Divider -->
                <div class="flex justify-center mt-3 opacity-90 mix-blend-multiply">
                    <img src="{{ asset('images/divine.png') }}" class="w-[280px] object-contain" alt="divider">
                </div>
            </div>

            <!-- Products White Card -->
            <div class="relative w-full max-w-[440px] mx-auto bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] pt-12 pb-12 border border-white/60 mb-8 overflow-hidden text-center">

                <!-- 1. SẢN PHẨM CHÍNH -->
                <div class="flex justify-center relative z-10 mb-6">
                    <x-profile.badge>SẢN PHẨM CHÍNH</x-profile.badge>
                </div>

                <div class="space-y-4 text-[18px] text-gray-900 leading-relaxed font-tinos px-6 mb-12">
                    {!! $coreProductsHtml !!}
                    @if(!empty($productGallery1Urls))
                <div class="w-full max-w-[440px] mx-auto mt-4 mb-8 overflow-hidden rounded-xl shadow-md border border-gray-100">
                    <div class="px-4 py-3 border-b border-gray-100 bg-white">
                        <div class="font-bold text-gray-900">Sản phẩm Chính</div>
                    </div>
                    <swiper-container pagination="true" pagination-clickable="true" loop="true" autoplay-delay="3000" class="w-full">
                        @foreach($productGallery1Urls as $idx => $img)
                            <swiper-slide>
                                <img src="{{ $img }}" class="w-full aspect-[4/5] object-cover" alt="Gallery sản phẩm 1 - {{ $idx + 1 }}">
                            </swiper-slide>
                        @endforeach
                    </swiper-container>
                </div>
            @endif
                </div>

                <!-- 2. DỊCH VỤ ĐI KÈM -->
                <div class="flex justify-center relative z-10 mb-6">
                    <x-profile.badge>DỊCH VỤ ĐI KÈM</x-profile.badge>
                </div>

                <div class="space-y-4 text-[18px] text-gray-900 leading-relaxed font-tinos px-6 mb-12">
                    {!! $accompanyingServicesHtml !!}
                    @if(!empty($productGallery2Urls))
                <div class="w-full max-w-[440px] mx-auto mt-4 mb-8 overflow-hidden rounded-xl shadow-md border border-gray-100">
                    <div class="px-4 py-3 border-b border-gray-100 bg-white">
                        <div class="font-bold text-gray-900">Dịch vụ đi kèm</div>
                    </div>
                    <swiper-container pagination="true" pagination-clickable="true" loop="true" autoplay-delay="3000" class="w-full">
                        @foreach($productGallery2Urls as $idx => $img)
                            <swiper-slide>
                                <img src="{{ $img }}" class="w-full aspect-[4/5] object-cover" alt="Gallery dịch vụ - {{ $idx + 1 }}">
                            </swiper-slide>
                        @endforeach
                    </swiper-container>
                </div>
            @endif
                </div>

                <!-- 3. SẢN PHẨM NỔI BẬT -->
                <div class="flex justify-center relative z-10 mb-6">
                    <x-profile.badge>SẢN PHẨM NỔI BẬT</x-profile.badge>
                </div>

                <div class="space-y-4 text-[18px] text-gray-900 leading-relaxed font-tinos px-6">
                    {!! $highlightProductsHtml !!}
                    @if(!empty($productGallery3Urls))
                <div class="w-full max-w-[440px] mx-auto mt-4 mb-4 overflow-hidden rounded-xl shadow-md border border-gray-100">
                    <div class="px-4 py-3 border-b border-gray-100 bg-white">
                        <div class="font-bold text-gray-900">Sản phẩm nổi bật</div>
                    </div>
                    <swiper-container pagination="true" pagination-clickable="true" loop="true" autoplay-delay="3000" class="w-full">
                        @foreach($productGallery3Urls as $idx => $img)
                            <swiper-slide>
                                <img src="{{ $img }}" class="w-full aspect-[4/5] object-cover" alt="Gallery sản phẩm - {{ $idx + 1 }}">
                            </swiper-slide>
                        @endforeach
                    </swiper-container>
                </div>
            @endif
                </div>

            </div>







        </div>
    </section>
    @endif

    <!-- BẢNG GAINS SECTION -->
    @if($hasGainsSection)
    <section id="bang-gains" class="relative w-full py-12 bg-gray-50 bg-cover bg-center" style="background-image: url('{{ asset('images/bg-pattern.jpg') }}');">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-white/40"></div>

        <div class="relative z-10 px-5">
            <!-- Section Header -->
            <div class="text-center mb-10">
                <h2 class="text-[26px] md:text-3xl font-philosopher font-bold text-[#D31215] uppercase tracking-wide drop-shadow-sm">
                    BẢNG GAINS
                </h2>
                <!-- Decorative Image Divider -->
                <div class="flex justify-center mt-3 opacity-90 mix-blend-multiply">
                    <img src="{{ asset('images/divine.png') }}" class="w-[280px] object-contain" alt="divider">
                </div>
            </div>

            <!-- GAINS White Card -->
            <div class="relative w-full max-w-[440px] mx-auto bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] pt-12 pb-12 border border-white/60 mb-8 overflow-hidden">

                <!-- Goals -->
                <div class="flex justify-center relative z-10 mb-6">
                    <x-profile.badge>Goals &ndash; Mục tiêu</x-profile.badge>
                </div>

                <div class="text-[18px] text-gray-900 leading-relaxed font-tinos px-6">
                    <div class="space-y-2.5 pl-0">{!! $gGoalsHtml !!}</div>
                </div>

                <!-- Accomplishments -->
                <div class="flex justify-center relative z-10 mt-10 mb-6">
                    <x-profile.badge>Accomplishments &ndash; Thành tựu</x-profile.badge>
                </div>
                <div class="text-[18px] text-gray-900 leading-relaxed font-tinos px-6">
                    <div class="space-y-3 pl-0">{!! $aAccomplishmentsHtml !!}</div>
                </div>

                <!-- Interests -->
                <div class="flex justify-center relative z-10 mt-10 mb-6">
                    <x-profile.badge>Interests &ndash; Sở thích</x-profile.badge>
                </div>
                <div class="text-[18px] text-gray-900 leading-relaxed font-tinos px-6">
                    <div class="space-y-3 pl-0">{!! $iInterestsHtml !!}</div>
                </div>

                <!-- Networks -->
                <div class="flex justify-center relative z-10 mt-10 mb-6">
                    <x-profile.badge>Networks &ndash; Mạng lưới quan hệ</x-profile.badge>
                </div>
                <div class="text-[18px] text-gray-900 leading-relaxed font-tinos px-6">
                    <div class="space-y-3 pl-0">{!! $nNetworksHtml !!}</div>
                </div>

                <!-- Skills -->
                <div class="flex justify-center relative z-10 mt-10 mb-6">
                    <x-profile.badge>Skills &ndash; Kỹ năng</x-profile.badge>
                </div>
                <div class="text-[18px] text-gray-900 leading-relaxed font-tinos px-6">
                    <div class="space-y-3 pl-0">{!! $sSkillsHtml !!}</div>
                </div>

            </div>
        </div>
    </section>
    @endif

    <!-- IDEAL REFERRAL SECTION -->
    @if($hasReferralSection)
    <section id="referral" class="relative w-full py-12 bg-gray-50 bg-cover bg-center" style="background-image: url('{{ asset('images/bg-pattern.jpg') }}');">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-white/40"></div>

        <div class="relative z-10 px-5">
            <!-- Section Header -->
            <div class="text-center mb-10">
                <h2 class="text-[26px] md:text-3xl font-philosopher font-bold text-[#D31215] uppercase tracking-wide drop-shadow-sm">
                    REFERAL LÝ TƯỞNG
                </h2>
                <!-- Decorative Image Divider -->
                <div class="flex justify-center mt-3 opacity-90 mix-blend-multiply">
                    <img src="{{ asset('images/divine.png') }}" class="w-[280px] object-contain" alt="divider">
                </div>
            </div>

            <!-- Referral White Card -->
            <div class="relative w-full max-w-[440px] mx-auto bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] pt-8 pb-8 border border-white/60 mb-8 overflow-hidden">
                <div class="text-[18px] text-gray-900 leading-relaxed font-tinos px-7">
                    {!! $idealReferralHtml !!}
                </div>
            </div>
        </div>

        <!-- CAM KẾT TRONG BNI SECTION -->
        @if($hasContent($commitmentHtml))
        <div class="relative z-10 px-5 mt-4">
            <div class="text-center mb-10">
                <h2 class="text-[26px] md:text-3xl font-philosopher font-bold text-[#D31215] uppercase tracking-wide drop-shadow-sm">
                    CAM KẾT TRONG BNI
                </h2>
                <div class="flex items-center justify-center mt-3 gap-1.5 text-[#c5a059]">
                    <i class="bi bi-diamond-fill text-[6px]"></i>
                    <div class="w-16 h-[1px] bg-[#c5a059]"></div>
                    <i class="bi bi-diamond-fill text-[8px]"></i>
                    <div class="w-1 h-1 bg-[#c5a059] rounded-full mx-0.5"></div>
                    <i class="bi bi-diamond-fill text-[8px]"></i>
                    <div class="w-16 h-[1px] bg-[#c5a059]"></div>
                    <i class="bi bi-diamond-fill text-[6px]"></i>
                </div>
            </div>
            <div class="relative w-full max-w-[440px] mx-auto bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] pt-8 pb-8 border border-white/60 mb-8 overflow-hidden">
                <div class="text-[18px] text-gray-900 leading-relaxed font-tinos px-7">
                    {!! $commitmentHtml !!}
                </div>
            </div>
        </div>
        @endif

        <!-- MONG MUỐN ĐƯỢC GIỚI THIỆU SECTION -->
        @if($hasContent($wishesHtml))
        <div class="relative z-10 px-5 mt-4">
            <div class="text-center mb-10">
                <h2 class="text-[26px] md:text-3xl font-philosopher font-bold text-[#D31215] uppercase tracking-wide drop-shadow-sm">
                    MONG MUỐN ĐƯỢC GIỚI THIỆU
                </h2>
                <div class="flex items-center justify-center mt-3 gap-1.5 text-[#c5a059]">
                    <i class="bi bi-diamond-fill text-[6px]"></i>
                    <div class="w-16 h-[1px] bg-[#c5a059]"></div>
                    <i class="bi bi-diamond-fill text-[8px]"></i>
                    <div class="w-1 h-1 bg-[#c5a059] rounded-full mx-0.5"></div>
                    <i class="bi bi-diamond-fill text-[8px]"></i>
                    <div class="w-16 h-[1px] bg-[#c5a059]"></div>
                    <i class="bi bi-diamond-fill text-[6px]"></i>
                </div>
            </div>
            <div class="relative w-full max-w-[440px] mx-auto bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] pt-8 pb-8 border border-white/60 mb-8 overflow-hidden">
                <div class="text-[18px] text-gray-900 leading-relaxed font-tinos px-7">
                    {!! $wishesHtml !!}
                </div>
            </div>
        </div>
        @endif

        <!-- BẢNG ĐẶT HỢP TÁC MQH SECTION -->
        @if($hasContent($coreProductsHtml) || $hasContent($accompanyingServicesHtml) || $hasContent($highlightProductsHtml))
        <div class="relative z-10 px-5 mt-4">
            <div class="text-center mb-10">
                <h2 class="text-[26px] md:text-3xl font-philosopher font-bold text-[#D31215] uppercase tracking-wide drop-shadow-sm">
                    BẢNG ĐẶT HỢP TÁC MQH
                </h2>
                <div class="flex items-center justify-center mt-3 gap-1.5 text-[#c5a059]">
                    <i class="bi bi-diamond-fill text-[6px]"></i>
                    <div class="w-16 h-[1px] bg-[#c5a059]"></div>
                    <i class="bi bi-diamond-fill text-[8px]"></i>
                    <div class="w-1 h-1 bg-[#c5a059] rounded-full mx-0.5"></div>
                    <i class="bi bi-diamond-fill text-[8px]"></i>
                    <div class="w-16 h-[1px] bg-[#c5a059]"></div>
                    <i class="bi bi-diamond-fill text-[6px]"></i>
                </div>
            </div>
            <div class="relative w-full max-w-[440px] mx-auto bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] pt-10 pb-10 border border-white/60 mb-8 overflow-hidden">

                <div class="text-[18px] text-gray-900 leading-relaxed font-tinos px-7">
                    <h3 class="font-bold text-[16.5px] mb-4">1. Mô tả sản phẩm / dịch vụ công ty cung cấp</h3>
                    <ul class="list-disc pl-2 space-y-2.5 marker:text-black mb-8">
                        @foreach($profile->main_products as $item)
                            <li class="pl-1 leading-tight">{{ $item }}</li>
                        @endforeach
                    </ul>

                    <h3 class="font-bold text-[16.5px] mb-4">Dịch vụ đi kèm</h3>
                    <ul class="list-disc pl-2 space-y-2.5 marker:text-black mb-8">
                        @foreach($profile->accompanying_services as $item)
                            <li class="pl-1 leading-tight">{{ $item }}</li>
                        @endforeach
                    </ul>

                    <h3 class="font-bold text-[16.5px] mb-4">Sản phẩm tiêu biểu</h3>
                    <ul class="list-disc pl-2 space-y-2.5 marker:text-black">
                        @foreach($profile->outstanding_products as $item)
                            <li class="pl-1 leading-tight">{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
        @endif

        <!-- CONTACT SPHERE SECTION -->
        @if($hasContent($sphereHtml))
        <div class="relative z-10 px-5 mt-4">
            <div class="text-center mb-10">
                <h2 class="text-[26px] md:text-3xl font-philosopher font-bold text-[#D31215] uppercase tracking-wide drop-shadow-sm">
                    CONTACT SPHERE
                </h2>
                <div class="flex items-center justify-center mt-3 gap-1.5 text-[#c5a059]">
                    <i class="bi bi-diamond-fill text-[6px]"></i>
                    <div class="w-16 h-[1px] bg-[#c5a059]"></div>
                    <i class="bi bi-diamond-fill text-[8px]"></i>
                    <div class="w-1 h-1 bg-[#c5a059] rounded-full mx-0.5"></div>
                    <i class="bi bi-diamond-fill text-[8px]"></i>
                    <div class="w-16 h-[1px] bg-[#c5a059]"></div>
                    <i class="bi bi-diamond-fill text-[6px]"></i>
                </div>
            </div>
            <div class="relative w-full max-w-[440px] mx-auto bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] pt-8 pb-8 border border-white/60 mb-8 overflow-hidden">
                <div class="text-[18px] text-gray-900 leading-relaxed font-tinos px-7">
                    {!! $sphereHtml !!}
                </div>
            </div>
        </div>
        @endif
    </section>
    @endif

    <!-- THANK YOU BANNER -->
    <section class="relative w-full aspect-[4/5] md:max-w-md mx-auto overflow-hidden bg-black mt-8 text-white flex flex-col justify-between items-center py-10" style="background-image: url('{{ $thanksBackgroundUrl }}'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-black/60"></div>

        <div class="relative z-10 mt-auto flex flex-col items-center gap-1.5 font-philosopher font-bold tracking-widest drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] pb-8 text-center px-4">
            <div class="text-[17px] md:text-lg uppercase">CẢM ƠN QUÝ VỊ</div>
            <div class="text-[15px] md:text-base uppercase">ĐÃ ĐỌC HẾT PHẦN GIỚI THIỆU</div>
            <div class="text-[13px] md:text-[15px] text-[#eacf82] mt-4 font-sans tracking-normal">Designed by THT MEDIA</div>
        </div>
    </section>

@endsection
