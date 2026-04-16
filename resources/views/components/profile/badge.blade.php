<div {{ $attributes->merge(['class' => 'rounded-full p-[3px] bg-gradient-to-r from-[#d4af37] via-[#fff5c3] to-[#c5a059] shadow-[0_10px_25px_rgba(0,0,0,0.25)]']) }}>
    <div
        class="bg-white px-5 md:px-7 py-2.5 rounded-full w-full h-full flex items-center justify-center shadow-[inset_0_3px_8px_rgba(0,0,0,0.15)]">
        <h3 class="text-[#001D3D] font-tinos font-black tracking-wider text-[18px] sm:text-[20px]">{{ $slot }}</h3>
    </div>
</div>