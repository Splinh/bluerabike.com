<?php

/**
 * Landing Page: Cơ hội hợp tác - Kinh doanh xe điện 2026
 * Template Name: Landing - Cơ hội hợp tác
 * Template Post Type: page
 *
 * @author SPL
 */

\defined('ABSPATH') || die;

get_header('cohoi');
?>

<!-- ===== HERO SECTION ===== -->
<section class="lp-hero" id="lp-hero">
    <div class="lp-hero__bg">
        <div class="lp-hero__overlay"></div>
    </div>
    <div class="container lp-hero__content">
        <span class="lp-hero__badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
            </svg>
            Cơ hội kinh doanh siêu hấp dẫn 2026
        </span>
        <h1 class="lp-hero__title">Chuỗi Nhượng Quyền Xe Điện Chính Hãng<br><span>Đảm Bảo 100%</span></h1>
        <p class="lp-hero__desc">Nhà sản xuất uy tín – Đa dạng mẫu mã – Giá tốt nhất thị trường</p>
        <div class="lp-hero__cta">
            <a href="#lp-form" class="lp-btn lp-btn--primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="8.5" cy="7" r="4" />
                    <line x1="20" y1="8" x2="20" y2="14" />
                    <line x1="23" y1="11" x2="17" y2="11" />
                </svg>
                Đăng ký đại lý ngay
            </a>
            <a href="#lp-benefits" class="lp-btn lp-btn--outline">Tìm hiểu thêm</a>
        </div>

        <!-- Stats -->
        <div class="lp-hero__stats">
            <div class="lp-hero__stat">
                <span class="lp-hero__stat-number" data-count="500">500+</span>
                <span class="lp-hero__stat-label">Hệ thống đại lý toàn quốc</span>
            </div>
            <div class="lp-hero__stat">
                <span class="lp-hero__stat-number">15+</span>
                <span class="lp-hero__stat-label">Năm kinh nghiệm<br>& 3 nhà máy sản xuất</span>
            </div>
            <div class="lp-hero__stat">
                <span class="lp-hero__stat-number">20-40%</span>
                <span class="lp-hero__stat-label">Lợi nhuận siêu hấp dẫn</span>
            </div>
            <div class="lp-hero__stat">
                <span class="lp-hero__stat-number">100%</span>
                <span class="lp-hero__stat-label">Hỗ trợ từ A–Z<br>Setup cửa hàng chuẩn hãng</span>
            </div>
        </div>
    </div>
    <!-- Scroll indicator -->
    <div class="lp-hero__scroll">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M19 12l-7 7-7-7" />
        </svg>
    </div>
</section>

<!-- ===== FEATURES BAR ===== -->
<section class="lp-features" id="lp-features">
    <div class="container">
        <div class="lp-features__grid">
            <div class="lp-features__item">
                <div class="lp-features__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="1" y="3" width="15" height="13" />
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
                        <circle cx="5.5" cy="18.5" r="2.5" />
                        <circle cx="18.5" cy="18.5" r="2.5" />
                    </svg>
                </div>
                <h3>Miễn Phí</h3>
                <p>GIAO HÀNG</p>
            </div>
            <div class="lp-features__item">
                <div class="lp-features__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                </div>
                <h3>Bảo Hành</h3>
                <p>36 THÁNG</p>
            </div>
            <div class="lp-features__item">
                <div class="lp-features__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polyline points="1 4 1 10 7 10" />
                        <polyline points="23 20 23 14 17 14" />
                        <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15" />
                    </svg>
                </div>
                <h3>Đầu Tư An Toàn</h3>
                <p>MIỄN PHÍ ĐỔI TRẢ</p>
            </div>
            <div class="lp-features__item">
                <div class="lp-features__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                    </svg>
                </div>
                <h3>Hỗ Trợ Đào Tạo</h3>
                <p>KỸ THUẬT BÁN HÀNG</p>
            </div>
            <div class="lp-features__item">
                <div class="lp-features__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <path d="M12 8v4M12 16h.01" />
                    </svg>
                </div>
                <h3>Bảo Vệ Vùng</h3>
                <p>ĐỘC QUYỀN KHU VỰC</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== BRAND INTRO ===== -->
<section class="lp-brand" id="lp-brand">
    <div class="container">
        <div class="lp-brand__inner">
            <div class="lp-brand__text">
                <span class="lp-section-label">Về thương hiệu</span>
                <h2 class="lp-section-title">Xe điện <span>Bluera Việt Nhật</span></h2>
                <p>Bluera Việt Nhật là thương hiệu xe điện ứng dụng công nghệ hiện đại, chuyên cung cấp các dòng <strong>xe đạp điện, xe máy điện và xe ba bánh điện</strong> chất lượng cao, vận hành bền bỉ, tiết kiệm năng lượng và tích hợp kết nối APP thông minh.</p>
                <p>Thương hiệu sở hữu <strong>nhà máy sản xuất quy mô lớn</strong> với dây chuyền lắp ráp hiện đại, đảm bảo tiêu chuẩn chất lượng trước khi sản phẩm đưa ra thị trường.</p>
                <p>Với định hướng mở rộng thị trường, Bluera Việt Nhật tiếp tục phát triển <strong>mạng lưới đại lý trên toàn quốc</strong>, mang đến cơ hội hợp tác kinh doanh tiềm năng và bền vững cho các đối tác.</p>
            </div>
            <div class="lp-brand__visual">
                <div class="lp-brand__card">
                    <div class="lp-brand__card-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 16v-4M12 8h.01" />
                        </svg>
                    </div>
                    <h4>Xu hướng xe điện 2026</h4>
                    <p>Thị trường xe điện Việt Nam đang tăng trưởng mạnh mẽ nhờ tiết kiệm chi phí, thân thiện môi trường, phù hợp với học sinh, sinh viên và người đi làm.</p>
                </div>
                <div class="lp-brand__card lp-brand__card--accent">
                    <div class="lp-brand__card-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </div>
                    <h4>Công nghệ hiện đại</h4>
                    <p>Tích hợp APP thông minh, dây chuyền lắp ráp hiện đại, đảm bảo tiêu chuẩn chất lượng cao nhất cho người dùng.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== POLICIES ===== -->
<section class="lp-policies" id="lp-policies">
    <div class="container">
        <span class="lp-section-label">Năm 2026</span>
        <h2 class="lp-section-title">Chính sách hợp tác <span>nổi bật</span></h2>

        <div class="lp-policies__grid">
            <div class="lp-policies__card lp-policies__card--featured">
                <div class="lp-policies__card-badge">HOT</div>
                <div class="lp-policies__card-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                    </svg>
                </div>
                <h3>Chính sách hỗ trợ linh hoạt</h3>
                <p>Chỉ cần mặt bằng phù hợp, Bluera hỗ trợ setup cửa hàng từ A–Z theo chuẩn, sẵn sàng vận hành ngay.</p>
            </div>
            <div class="lp-policies__card">
                <div class="lp-policies__card-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                </div>
                <h3>Hỗ trợ Marketing</h3>
                <p>Hỗ trợ chạy quảng cáo xuyên suốt quá trình kinh doanh, cung cấp nội dung – hình ảnh và đẩy mạnh quảng cáo vào mùa cao điểm.</p>
            </div>
            <div class="lp-policies__card">
                <div class="lp-policies__card-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
                <h3>Hỗ trợ đổi trả sản phẩm</h3>
                <p>Chính sách đổi trả linh hoạt, hỗ trợ xử lý sản phẩm trong suốt quá trình kinh doanh, giúp giảm rủi ro tồn kho.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== BENEFITS ===== -->
<section class="lp-benefits" id="lp-benefits">
    <div class="container">
        <span class="lp-section-label">Lợi ích đại lý</span>
        <h2 class="lp-section-title">Lợi ích khi trở thành đại lý <span>Bluera Việt Nhật</span></h2>
        <p class="lp-section-subtitle">Khi hợp tác cùng Bluera Việt Nhật, đại lý sẽ nhận được nhiều chính sách hỗ trợ hấp dẫn nhằm giúp việc kinh doanh trở nên dễ dàng và hiệu quả hơn.</p>

        <div class="lp-benefits__grid">
            <div class="lp-benefits__item">
                <div class="lp-benefits__num">01</div>
                <h4>Chiết khấu hấp dẫn & Thưởng doanh số</h4>
                <ul class="lp-benefits__detail">
                    <li>Chính sách giá cạnh tranh: Đảm bảo mức lợi nhuận tối ưu cho đại lý trên từng sản phẩm.</li>
                    <li>Chương trình ưu đãi: Áp dụng linh hoạt theo Tháng/Quý/Năm.</li>
                    <li>Thưởng vượt doanh số: Cơ chế thưởng nóng và quà tặng giá trị dành cho các đại lý có thành tích kinh doanh xuất sắc.</li>
                </ul>
            </div>
            <div class="lp-benefits__item">
                <div class="lp-benefits__num">02</div>
                <h4>Nguồn hàng đa dạng & Ổn định</h4>
                <ul class="lp-benefits__detail">
                    <li>Hệ thống sản xuất: Sở hữu 03 nhà máy quy mô lớn, đảm bảo cung ứng hàng hóa liên tục, không đứt gãy.</li>
                    <li>Dẫn đầu xu hướng: Danh mục sản phẩm phong phú với những mẫu mã "Hot" nhất thị trường, được cập nhật thường xuyên để đáp ứng thị hiếu người dùng.</li>
                </ul>
            </div>
            <div class="lp-benefits__item">
                <div class="lp-benefits__num">03</div>
                <h4>Hỗ trợ Marketing & Quảng bá</h4>
                <ul class="lp-benefits__detail">
                    <li>Cung cấp bộ nhận diện thương hiệu, hình ảnh, video chuyên nghiệp và nội dung quảng cáo đa nền tảng.</li>
                    <li>Đồng hành cùng đại lý trong các chiến dịch truyền thông lớn để gia tăng độ phủ và tìm kiếm khách hàng tiềm năng.</li>
                </ul>
            </div>
            <div class="lp-benefits__item">
                <div class="lp-benefits__num">04</div>
                <h4>Đào tạo bán hàng chuyên nghiệp</h4>
                <ul class="lp-benefits__detail">
                    <li>Hướng dẫn bài bản về quy trình bán hàng, kỹ năng tư vấn và chốt sale hiệu quả.</li>
                    <li>Chuyển giao quy trình chăm sóc khách hàng chuẩn mực để xây dựng tệp khách hàng trung thành.</li>
                </ul>
            </div>
            <div class="lp-benefits__item">
                <div class="lp-benefits__num">05</div>
                <h4>Hỗ trợ kỹ thuật & Bảo hành</h4>
                <ul class="lp-benefits__detail">
                    <li>Đào tạo kỹ thuật sửa chữa chuyên sâu cho đội ngũ nhân viên của đại lý.</li>
                    <li>Chính sách bảo hành chính hãng nhanh chóng, cung cấp linh kiện thay thế đầy đủ và kịp thời.</li>
                </ul>
            </div>
            <div class="lp-benefits__item">
                <div class="lp-benefits__num">06</div>
                <h4>Tối ưu sản phẩm & Giải pháp công nghệ</h4>
                <ul class="lp-benefits__detail">
                    <li>Mô hình kinh doanh mới: Gia tăng nguồn thu thông qua các dịch vụ giá trị gia tăng như cho thuê xe/thiết bị.</li>
                    <li>Quản lý thông minh: Tăng hiệu quả lợi nhuận thông qua App quản lý độc quyền của công ty (hoàn toàn miễn phí), giúp theo dõi vận hành và tối ưu hóa doanh thu dễ dàng.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ===== PROCESS STEPS ===== -->
<section class="lp-process" id="lp-process">
    <div class="container">
        <span class="lp-section-label">Quy trình</span>
        <h2 class="lp-section-title">Mô hình hợp tác <span>đơn giản</span></h2>
        <p class="lp-section-subtitle">Chỉ với vài bước đơn giản, bạn đã có thể sở hữu mô hình kinh doanh xe điện tiềm năng.</p>

        <div class="lp-process__timeline">
            <div class="lp-process__step">
                <div class="lp-process__step-num">1</div>
                <div class="lp-process__step-content">
                    <div class="lp-process__step-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                            <polyline points="10 9 9 9 8 9" />
                        </svg>
                    </div>
                    <h3>Điền Form Đăng Ký</h3>
                    <p>Cung cấp thông tin qua biểu mẫu trực tuyến của chúng tôi.</p>
                </div>
            </div>
            <div class="lp-process__step">
                <div class="lp-process__step-num">2</div>
                <div class="lp-process__step-content">
                    <div class="lp-process__step-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                    </div>
                    <h3>Tư Vấn & Thẩm Định</h3>
                    <p>Chuyên viên sẽ liên hệ, tư vấn chi tiết và trao đổi về điều kiện hợp tác.</p>
                </div>
            </div>
            <div class="lp-process__step">
                <div class="lp-process__step-num">3</div>
                <div class="lp-process__step-content">
                    <div class="lp-process__step-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </div>
                    <h3>Ký Hợp Đồng & Bắt Đầu</h3>
                    <p>Hoàn tất hợp đồng và chúng tôi sẽ hỗ trợ bạn setup để kinh doanh.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CONDITIONS ===== -->
<section class="lp-conditions" id="lp-conditions">
    <div class="container">
        <div class="lp-conditions__inner">
            <div class="lp-conditions__text">
                <span class="lp-section-label">Yêu cầu</span>
                <h2 class="lp-section-title">Điều kiện trở thành <span>đại lý</span></h2>
                <ul class="lp-conditions__list">
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Có mặt bằng kinh doanh phù hợp, vị trí thuận lợi
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Diện tích đủ để trưng bày các dòng xe điện
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Có năng lực tài chính và định hướng phát triển lâu dài
                    </li>

                </ul>
            </div>
            <div class="lp-conditions__cta">
                <div class="lp-conditions__cta-card">
                    <h3>Sẵn sàng bắt đầu?</h3>
                    <p>Bluera Việt Nhật luôn đặt mục tiêu phát triển bền vững cùng các đối tác. Cam kết đồng hành cùng đại lý trong suốt quá trình kinh doanh.</p>
                    <a href="#lp-form" class="lp-btn lp-btn--primary lp-btn--full">Đăng ký đại lý ngay</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== REGISTRATION FORM ===== -->
<section class="lp-form" id="lp-form">
    <div class="lp-form__bg"></div>
    <div class="container lp-form__inner">
        <div class="lp-form__header">
            <span class="lp-section-label lp-section-label--light">Đăng ký ngay</span>
            <h2 class="lp-section-title lp-section-title--light">Đăng ký tư vấn mở<br><span>Đại lý / Cửa hàng ủy quyền</span></h2>
        </div>
        <div class="lp-form__wrapper">
            <?php
            // Use CF7 if available
            $cf7_id = get_field('form_cf7_id', get_the_ID());
            if ($cf7_id) {
                echo \HD_Helper::doShortcode('contact-form-7', ['id' => $cf7_id]);
            } else {
                // AJAX form with nonce
            ?>
                <form class="lp-form__static" id="lp-registration-form" novalidate>
                    <?php wp_nonce_field('lp_form_nonce', '_lp_nonce', false); ?>
                    <div class="lp-form__row">
                        <div class="lp-form__field">
                            <input type="text" name="fullname" placeholder="Họ và tên *" required autocomplete="name">
                        </div>
                        <div class="lp-form__field">
                            <input type="tel" name="phone" placeholder="Số điện thoại *" required autocomplete="tel">
                        </div>
                    </div>
                    <div class="lp-form__row">
                        <div class="lp-form__field lp-form__field--full">
                            <input type="text" name="region" placeholder="Khu vực (Tỉnh/Thành phố) *" required autocomplete="address-level1">
                        </div>
                    </div>
                    <div class="lp-form__row">
                        <div class="lp-form__field lp-form__field--full">
                            <textarea name="message" placeholder="Nội dung cần tư vấn *" rows="3" required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="lp-btn lp-btn--primary lp-btn--lg" id="lp-submit-btn">
                        <svg class="lp-form__icon-send" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13" />
                            <polygon points="22 2 15 22 11 13 2 9 22 2" />
                        </svg>
                        <svg class="lp-form__icon-loading" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                            <circle cx="12" cy="12" r="10" stroke-dasharray="31.4" stroke-dashoffset="10">
                                <animateTransform attributeName="transform" type="rotate" dur="1s" from="0 12 12" to="360 12 12" repeatCount="indefinite" />
                            </circle>
                        </svg>
                        <span class="lp-form__btn-text">ĐĂNG KÝ</span>
                    </button>
                    <div class="lp-form__message" id="lp-form-message" style="display:none;"></div>
                </form>
            <?php } ?>
        </div>
    </div>
</section>

<!-- ===== FAQ ===== -->
<section class="lp-faq" id="lp-faq">
    <div class="container">
        <span class="lp-section-label">FAQ</span>
        <h2 class="lp-section-title">Những câu hỏi thường gặp khi bắt đầu <span>kinh doanh xe điện</span></h2>

        <div class="lp-faq__list">
            <div class="lp-faq__item active">
                <div class="lp-faq__question" role="button" aria-expanded="true">
                    <span>Các giấy tờ pháp lý cần thiết để mở đại lý xe điện</span>
                    <svg class="lp-faq__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
                <div class="lp-faq__answer">
                    <div class="lp-faq__answer-content">
                        <ul>
                            <li>Giấy chứng nhận đăng ký doanh nghiệp — bắt buộc cho mọi hình thức kinh doanh chính thống.</li>
                            <li>Giấy chứng nhận đăng ký hộ kinh doanh (nếu mở dưới dạng hộ cá thể).</li>
                            <li>Giấy chứng nhận đủ điều kiện phòng cháy chữa cháy (cho cửa hàng diện tích lớn hoặc kho hàng).</li>
                            <li>Giấy phép kinh doanh ngành nghề bán lẻ phương tiện giao thông.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="lp-faq__item">
                <div class="lp-faq__question" role="button" aria-expanded="false">
                    <span>Xe điện có cần giấy tờ xuất xứ, chất lượng không?</span>
                    <svg class="lp-faq__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
                <div class="lp-faq__answer">
                    <div class="lp-faq__answer-content">
                        <p>Có. Xe điện khi nhập khẩu hoặc sản xuất trong nước phải có giấy chứng nhận chất lượng (CQ) và giấy chứng nhận nguồn gốc xuất xứ (CO).</p>
                        <p>Ngoài ra, xe cũng cần đạt các tiêu chuẩn kỹ thuật về an toàn, điện áp, pin… theo quy định của Bộ GTVT.</p>
                    </div>
                </div>
            </div>

            <div class="lp-faq__item">
                <div class="lp-faq__question" role="button" aria-expanded="false">
                    <span>Có cần xin phép đặc biệt để bán xe điện không?</span>
                    <svg class="lp-faq__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
                <div class="lp-faq__answer">
                    <div class="lp-faq__answer-content">
                        <p>Tùy theo loại xe điện, nếu là xe thuộc dạng cần đăng ký (xe máy điện), cần đảm bảo xe được kiểm định và có giấy phép lưu hành. Đối với xe đạp điện thì không yêu cầu đăng ký.</p>
                        <p>Khi hợp tác với Bluera Việt Nhật, toàn bộ giấy tờ pháp lý và chứng nhận chất lượng sẽ được cung cấp đầy đủ cho đại lý.</p>
                    </div>
                </div>
            </div>

            <div class="lp-faq__item">
                <div class="lp-faq__question" role="button" aria-expanded="false">
                    <span>Thủ tục đăng ký trở thành đại lý như thế nào?</span>
                    <svg class="lp-faq__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
                <div class="lp-faq__answer">
                    <div class="lp-faq__answer-content">
                        <p>Bạn chỉ cần điền form đăng ký trên website, sau đó chuyên viên của Bluera Việt Nhật sẽ liên hệ tư vấn chi tiết về chính sách và điều kiện hợp tác phù hợp với quy mô kinh doanh của bạn.</p>
                        <p>Sau khi thống nhất các điều khoản, hai bên sẽ tiến hành ký hợp đồng và Bluera sẽ hỗ trợ setup cửa hàng để bắt đầu kinh doanh.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FINAL CTA ===== -->
<section class="lp-cta" id="lp-cta">
    <div class="container">
        <div class="lp-cta__inner">
            <h2>Bắt đầu kinh doanh xe điện <span>ngay hôm nay!</span></h2>
            <p>Hệ thống đại lý Bluera Việt Nhật đang ngày càng mở rộng trên khắp các tỉnh thành, tạo nên mạng lưới phân phối xe điện uy tín và chuyên nghiệp.</p>
            <div class="lp-cta__buttons">
                <a href="#lp-form" class="lp-btn lp-btn--primary lp-btn--lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="8.5" cy="7" r="4" />
                        <line x1="20" y1="8" x2="20" y2="14" />
                        <line x1="23" y1="11" x2="17" y2="11" />
                    </svg>
                    Đăng ký đại lý
                </a>
                <a href="tel:0933555202" class="lp-btn lp-btn--outline-white lp-btn--lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    Hotline: 0933 555 202
                </a>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ========== FAQ Accordion ==========
        const faqList = document.querySelector('.lp-faq__list');
        if (faqList) {
            faqList.addEventListener('click', function(e) {
                const question = e.target.closest('.lp-faq__question');
                if (!question) return;
                const item = question.parentElement;
                const isActive = item.classList.contains('active');

                faqList.querySelectorAll('.lp-faq__item').forEach(i => {
                    i.classList.remove('active');
                    i.querySelector('.lp-faq__question').setAttribute('aria-expanded', 'false');
                });

                if (!isActive) {
                    item.classList.add('active');
                    question.setAttribute('aria-expanded', 'true');
                }
            });
        }

        // ========== Smooth Scroll ==========
        document.querySelectorAll('a[href^="#lp-"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerOffset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // ========== Scroll Animations ==========
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, {
            threshold: 0.15
        });

        document.querySelectorAll('.lp-benefits__item, .lp-process__step, .lp-policies__card, .lp-features__item, .lp-hero__stat').forEach(el => {
            observer.observe(el);
        });

        // ========== AJAX Form Submit ==========
        const form = document.getElementById('lp-registration-form');
        if (!form) return;

        const btn = document.getElementById('lp-submit-btn');
        const iconSend = form.querySelector('.lp-form__icon-send');
        const iconLoading = form.querySelector('.lp-form__icon-loading');
        const btnText = form.querySelector('.lp-form__btn-text');
        const msgBox = document.getElementById('lp-form-message');

        function setLoading(loading) {
            btn.disabled = loading;
            if (iconSend) iconSend.style.display = loading ? 'none' : '';
            if (iconLoading) iconLoading.style.display = loading ? '' : 'none';
            if (btnText) btnText.textContent = loading ? 'ĐANG GỬI...' : 'ĐĂNG KÝ';
            btn.style.opacity = loading ? '0.7' : '1';
        }

        function showMessage(text, isSuccess) {
            msgBox.textContent = text;
            msgBox.style.display = 'block';
            msgBox.style.marginTop = '16px';
            msgBox.style.padding = '14px 20px';
            msgBox.style.borderRadius = '10px';
            msgBox.style.fontSize = '14px';
            msgBox.style.fontWeight = '600';
            msgBox.style.textAlign = 'center';

            if (isSuccess) {
                msgBox.style.background = 'rgba(34,197,94,0.15)';
                msgBox.style.color = '#22c55e';
                msgBox.style.border = '1px solid rgba(34,197,94,0.3)';
            } else {
                msgBox.style.background = 'rgba(239,68,68,0.15)';
                msgBox.style.color = '#ef4444';
                msgBox.style.border = '1px solid rgba(239,68,68,0.3)';
            }
        }

        // ========== Realtime Phone Validation ==========
        const phoneInput = form.querySelector('[name="phone"]');
        const phoneField = phoneInput.closest('.lp-form__field');

        // Only allow digits, +, spaces, dashes
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\s\-\.]/g, '');
        });

        function validatePhone(value) {
            const clean = value.replace(/[\s\-\.]/g, '');
            return /^(0|\+84)[0-9]{9,10}$/.test(clean);
        }

        function setFieldError(field, hasError) {
            if (hasError) {
                field.style.border = '2px solid #ef4444';
                field.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.15)';
            } else {
                field.style.border = '';
                field.style.boxShadow = '';
            }
        }

        phoneInput.addEventListener('blur', function() {
            const val = this.value.trim();
            if (val && !validatePhone(val)) {
                setFieldError(phoneField, true);
                showMessage('Số điện thoại không hợp lệ. VD: 0933555202', false);
            } else {
                setFieldError(phoneField, false);
                msgBox.style.display = 'none';
            }
        });

        // Clear error on focus
        phoneInput.addEventListener('focus', function() {
            setFieldError(phoneField, false);
        });

        // ========== Required fields highlight ==========
        form.querySelectorAll('[required]').forEach(input => {
            input.addEventListener('blur', function() {
                const field = this.closest('.lp-form__field');
                if (!this.value.trim()) {
                    setFieldError(field, true);
                } else {
                    setFieldError(field, false);
                }
            });
            input.addEventListener('focus', function() {
                setFieldError(this.closest('.lp-form__field'), false);
            });
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Client-side validation
            const fullname = form.querySelector('[name="fullname"]').value.trim();
            const phone = form.querySelector('[name="phone"]').value.trim();
            const region = form.querySelector('[name="region"]').value.trim();
            const message = form.querySelector('[name="message"]').value.trim();

            // Highlight empty fields
            let hasEmpty = false;
            form.querySelectorAll('[required]').forEach(input => {
                const field = input.closest('.lp-form__field');
                if (!input.value.trim()) {
                    setFieldError(field, true);
                    hasEmpty = true;
                }
            });

            if (hasEmpty) {
                showMessage('Vui lòng điền đầy đủ tất cả các trường bắt buộc.', false);
                return;
            }

            if (!validatePhone(phone)) {
                setFieldError(phoneField, true);
                showMessage('Số điện thoại không hợp lệ. VD: 0933555202', false);
                phoneInput.focus();
                return;
            }

            // Send AJAX
            setLoading(true);
            msgBox.style.display = 'none';

            const formData = new FormData(form);
            formData.append('action', 'lp_submit_form');
            formData.append('_nonce', form.querySelector('[name="_lp_nonce"]').value);

            fetch(window.hdConfig?.ajaxUrl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData,
                })
                .then(res => res.json())
                .then(json => {
                    setLoading(false);
                    if (json.success) {
                        showMessage(json.data.message || 'Đăng ký thành công!', true);
                        form.reset();
                        // Hide success after 8s
                        setTimeout(() => {
                            msgBox.style.display = 'none';
                        }, 8000);
                    } else {
                        showMessage(json.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.', false);
                    }
                })
                .catch(() => {
                    setLoading(false);
                    showMessage('Lỗi kết nối. Vui lòng kiểm tra mạng và thử lại.', false);
                });
        });
    });
</script>

<?php get_footer('cohoi'); ?>