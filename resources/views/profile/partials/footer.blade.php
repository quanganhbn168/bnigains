<footer class="mt-12 w-full text-center pb-8 px-6">
    <div class="border-t border-gray-100 pt-6">
        <p class="text-[13px] text-gray-500 font-medium">
            &copy; {{ date('Y') }} {{ $profile->full_name ?? 'BNI Member' }}. All rights reserved.
        </p>
        <p class="text-[11px] text-gray-400 mt-1 flex items-center justify-center gap-1">
            Thiết kế cho <span class="font-bold text-red-600 tracking-wider">BNI</span>
            {{ $profile->chapter_name ?? '' }}
        </p>
    </div>
</footer>
