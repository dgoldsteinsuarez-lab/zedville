<article class="tailCard bg-white py-6 lg:py-10 px-6 lg:px-8 max-w-[900px] w-full border border-[#D2DDDB] rounded-lg">
    @php
        use Carbon\Carbon;
        if ($mail) {
        $mailDate = $mail->created_at;
        if ($mailDate->isToday()) {
            $displayDate = __('mailbox.today', ['date' => $mailDate->isoFormat('LLL')]);
        } elseif ($mailDate->isYesterday()) {
            $displayDate = __('mailbox.yesterday', ['date' => $mailDate->isoFormat('LLL')]);
        } else {
            $displayDate = $mailDate->isoFormat('LLL');
        }
    @endphp
    
    <div id="primaryDiv">
        <header class="mailDtlshead mb-12" >
            <div class="dateTime text-xs leading-[18px] text-[#5C5C5C]">{{ $displayDate }}</div>
            <h4 class="text-[24px] md:text-[28px] lg:text-[32px] font-semibold leading-tight">{{ $mail->subject }}</h4>
        </header>
        <div class="mailBodyTxt text-md xl:text-base leading-[18px] text-black space-y-4">
            {!! $mail->content !!}
        </div>
    </div>
    <!--<div class="sendBy mt-16">
        <div class="flex w-fit items-center space-x-2 bg-[#EDF4F3] border border-[#D2DDDB] rounded-md py-1 px-3">
            <span class="icon">[SVG ICON]</span>
            <span class="stxt font-medium">Sent by :</span>
            <span class="stxtname font-semibold">Administrator</span>
        </div>
    </div>-->
    <div class="actionsBtns flex flex-wrap gap-x-3 gap-y-2 mt-12">
        <button class="whiteBtn" onclick="updateemailstatus({{ $mail->id }}, 'primary')">{{ __('mailbox.restore') }}</button>
    </div>
    @php }else{ echo e(__('mailbox.no_mail'));} @endphp
</article>
