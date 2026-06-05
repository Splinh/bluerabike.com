<?php
defined('ABSPATH') || exit;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'cohoi_faqs') {
    return;
}

$section_title = $args['section_title'] ?? 'Câu Hỏi Thường Gặp';
$description   = $args['description'] ?? '';
$list_faqs     = $args['list_faqs'] ?? [];
?>

<section class="cohoifaq-section py-16 bg-slate-50" id="faq-container">
    <div class="container max-w-3xl mx-auto px-4">

        <?php if ($section_title || $description) : ?>
            <div class="text-center mb-12">
                <?php if ($section_title) : ?>
                    <h2 class="heading-title">
                        <?= esc_html($section_title) ?>
                    </h2>
                <?php endif; ?>

                <?php if ($description) : ?>
                    <p class="text-lg text-slate-600 max-w-xl mx-auto">
                        <?= esc_html($description) ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($list_faqs)) : ?>
            <div class="faq-list">
                <?php foreach ($list_faqs as $index => $faq) :
                    $question = $faq['question'] ?? '';
                    $answer   = $faq['answer'] ?? '';
                    $active   = $index === 0 ? 'active' : '';
                ?>
                    <div class="faq-item <?= esc_attr($active) ?>">
                        <div class="faq-question" role="button" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>">
                            <span><?= esc_html($question) ?></span>
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                        <div class="faq-answer">
                            <div class= "answer-content">
                                <?= wp_kses_post($answer) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>


.cohoifaq-section .faq-item {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    margin-bottom: 16px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    transition: all 0.3s ease-in-out;
    overflow: hidden;
}
.cohoifaq-section .faq-item:hover {
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    border-color: #cbd5e1;
}
.cohoifaq-section .faq-item.active {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px #bfdbfe;
}
.cohoifaq-section .faq-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    cursor: pointer;
    font-weight: 600;
    color: #1e293b;
}
.cohoifaq-section .faq-question .icon {
    width: 20px;
    height: 20px;
    transition: transform 0.3s ease;
    color: #64748b;
}
.cohoifaq-section .faq-answer {
    display: none;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.4s ease-in-out;
}
.cohoifaq-section .faq-item.active .faq-answer {
    display: grid;
}
.cohoifaq-section .faq-answer > div {
    overflow: hidden;
}
.cohoifaq-section .answer-content {
    padding: 0 24px 24px 24px;
    color: #475569;
    line-height: 1.6;
}
.cohoifaq-section .faq-item.active .faq-answer {
    
    grid-template-rows: 1fr;
}
.cohoifaq-section .faq-item.active .faq-question .icon {
    transform: rotate(180deg);
    color: #3b82f6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.faq-list');
    if (!container) return;
    container.addEventListener('click', function (e) {
        const question = e.target.closest('.faq-question');
        if (!question) return;
        const item = question.parentElement;
        const isActive = item.classList.contains('active');
        if (isActive) {
            item.classList.remove('active');
            question.setAttribute('aria-expanded', 'false');
        } else {
            item.classList.add('active');
            question.setAttribute('aria-expanded', 'true');
        }
    });
});
</script>
