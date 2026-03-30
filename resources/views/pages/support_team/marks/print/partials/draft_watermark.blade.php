@if(!empty($draft_watermark))
<style>
    .emaaree-draft-watermark {
        position: fixed;
        left: 50%;
        top: 44%;
        transform: translate(-50%, -50%) rotate(-32deg);
        font-size: clamp(48px, 12vw, 96px);
        font-weight: 900;
        letter-spacing: 0.08em;
        color: rgba(183, 28, 28, 0.12);
        z-index: 99999;
        pointer-events: none;
        white-space: nowrap;
        user-select: none;
    }
    @media print {
        .emaaree-draft-watermark {
            position: fixed;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
<div class="emaaree-draft-watermark" aria-hidden="true">DRAFT</div>
@endif
