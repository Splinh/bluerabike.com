<?php
/*
 * Plugin Name: DevVN - Trang trí Tết Việt Nam
 * Plugin URI: https://levantoan.com/san-pham/
 * Version: 1.0.10
 * Description: Trang trí Tết Việt Nam bằng câu đối, hoa đào, hoa mai, pháo hoa và các hỉnh ảnh tượng trưng cho ngày Tết truyền thống của Việt Nam
 * Author: Lê Văn Toản
 * Author URI: https://levantoan.com
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: devvn-tet-holiday
 * Domain Path: /languages
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !defined( 'DEVVN_TET_HOLIDAY_BASENAME' ) )
    define( 'DEVVN_TET_HOLIDAY_BASENAME', plugin_basename( __FILE__ ) );

if (!defined('DEVVN_TET_HOLIDAY_URL'))
    define('DEVVN_TET_HOLIDAY_URL', plugin_dir_url(__FILE__));

add_action( 'init', 'devvn_tet_load_textdomain' );
function devvn_tet_load_textdomain() {
    load_plugin_textdomain( 'devvn-tet-holiday', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

function devvn_tet_holiday_imgs( $img = 'style_5', $value = '' ) {
    $images = apply_filters( 'devvn_tet_holiday_imgs', array(
        'style_1' => array(
            'left' => DEVVN_TET_HOLIDAY_URL . 'images/left-1.png',
            'right' => DEVVN_TET_HOLIDAY_URL . 'images/right-1.png',
            'left_w' => 191,
            'right_w' => 191,
        ),
        'style_2' => array(
            'left' => DEVVN_TET_HOLIDAY_URL . 'images/left-2.png',
            'right' => DEVVN_TET_HOLIDAY_URL . 'images/right-2.png',
            'left_w' => 148,
            'right_w' => 148,
        ),
        'style_3' => array(
            'left' => DEVVN_TET_HOLIDAY_URL . 'images/left-3.png',
            'right' => DEVVN_TET_HOLIDAY_URL . 'images/right-3.png',
            'left_w' => 266,
            'right_w' => 181,
        ),
        'style_4' => array(
            'left' => DEVVN_TET_HOLIDAY_URL . 'images/left-4.png',
            'right' => DEVVN_TET_HOLIDAY_URL . 'images/right-4.png',
            'left_w' => 148,
            'right_w' => 148,
        ),
        'style_5' => array(
            'left' => DEVVN_TET_HOLIDAY_URL . 'images/left-5.png',
            'right' => DEVVN_TET_HOLIDAY_URL . 'images/right-5.png',
            'left_w' => 148,
            'right_w' => 148,
        ),
        'style_6' => array(
            'left' => DEVVN_TET_HOLIDAY_URL . 'images/thin-1.png',
            'right' => DEVVN_TET_HOLIDAY_URL . 'images/thin-2.png',
            'left_w' => 145,
            'right_w' => 145,
        ),
        'style_7' => array(
            'left' => DEVVN_TET_HOLIDAY_URL . 'images/left-ngo.png',
            'right' => DEVVN_TET_HOLIDAY_URL . 'images/right-ngo.png',
            'left_w' => 148,
            'right_w' => 148,
        ),
        'bottom_1' => array(
            'url' => DEVVN_TET_HOLIDAY_URL . 'images/bottom-1.png',
            'full' => 0,
            'w' => 320,
            'left' => 80,
        ),
        'bottom_2' => array(
            'url' => DEVVN_TET_HOLIDAY_URL . 'images/bottom-2.png',
            'full' => 1,
            'w' => 1600,
            'left' => 0,
        ),
        'dao' => array(
            'url' => DEVVN_TET_HOLIDAY_URL . 'images/hoadao.png',
            'w' => 15
        ),
        'mai' => array(
            'url' => DEVVN_TET_HOLIDAY_URL . 'images/hoamai.png',
            'w' => 30
        ),
    ) );
    if ( $value ) {
        return isset( $images[ $img ][ $value ] ) ? esc_attr( apply_filters( 'devvn_tet_holiday_imgs_value', $images[ $img ][ $value ], $img, $value ) ) : '';
    }
    return isset( $images[ $img ] ) ? (array) $images[ $img ] : '';
}

function devvn_tet_holiday_custom_scripts() {
    if ( ! wp_script_is( 'jquery' ) ) {
        wp_enqueue_script( 'jquery' );
    }
}
add_action( 'wp_enqueue_scripts', 'devvn_tet_holiday_custom_scripts' );

add_action( 'wp_footer', 'devvn_tet_holiday', 999 );
function devvn_tet_holiday() {

    $style = esc_attr( devvn_get_tet_holiday_options( 'style' ) );
    $left_width = (int) devvn_tet_holiday_imgs( $style, 'left_w' );
    $right_width = (int) devvn_tet_holiday_imgs( $style, 'right_w' );
    $zindex = absint( devvn_get_tet_holiday_options( 'zindex' ) );
    $container_width = absint( devvn_get_tet_holiday_options( 'container_width' ) );

    $bottom_style = esc_attr( devvn_get_tet_holiday_options( 'bottom_style' ) );
    $bottom_full = absint( devvn_tet_holiday_imgs( $bottom_style, 'full' ) );
    $bottom_left = absint( devvn_tet_holiday_imgs( $bottom_style, 'left' ) );
    $bottom_w = $bottom_full ? '100%' : absint( devvn_tet_holiday_imgs( $bottom_style, 'w' ) ) . 'px';

    $enable_firework = absint( devvn_get_tet_holiday_options( 'enable_firework' ) );
    $firework_color = sanitize_hex_color( devvn_get_tet_holiday_options( 'firework_color' ) );
    $firework_speed_mobile = absint( devvn_get_tet_holiday_options( 'firework_speed_mobile' ) );
    $firework_speed_pc = absint( devvn_get_tet_holiday_options( 'firework_speed_pc' ) );
    $firework_timer = absint( devvn_get_tet_holiday_options( 'firework_timer' ) );
    $enable_hoamaidao = esc_attr( devvn_get_tet_holiday_options( 'enable_hoamaidao' ) );
    $enable_audio = absint( devvn_get_tet_holiday_options( 'enable_audio' ) );

    if ( 'custom' !== $style ) {
        $left_url = esc_url_raw( devvn_tet_holiday_imgs( $style, 'left' ) );
        $right_url = esc_url_raw( devvn_tet_holiday_imgs( $style, 'right' ) );
    } else {
        $left_banner = absint( devvn_get_tet_holiday_options( 'left_banner' ) );
        $right_banner = absint( devvn_get_tet_holiday_options( 'right_banner' ) );

        $left_url = esc_url_raw( wp_get_attachment_image_url( $left_banner, 'full' ) );
        $right_url = esc_url_raw( wp_get_attachment_image_url( $right_banner, 'full' ) );

        $left_width = wp_get_attachment_metadata( $left_banner );
        $left_width = ( $left_width && ! is_wp_error( $left_width ) && is_array( $left_width ) && isset( $left_width['width'] ) ) ? absint( $left_width['width'] ) : '';

        $right_width = wp_get_attachment_metadata( $right_banner );
        $right_width = ( $right_width && ! is_wp_error( $right_width ) && is_array( $right_width ) && isset( $right_width['width'] ) ) ? absint( $right_width['width'] ) : '';
    }
    ?>
    <style>
        .tet_left img, .tet_right img {
            width: 100%;
            height: auto;
        }
        .tet_left, .tet_right {
            position: fixed;
            top: 0;
            left: 0;
            z-index: <?php echo absint( $zindex );?>;
            width: <?php echo absint( $left_width );?>px;
            pointer-events: none;
        }
        .tet_right {
            left: auto;
            right: 0;
            width: <?php echo absint( $right_width );?>px;
        }

        <?php if ( 3 === $enable_firework ) : ?>
        .firework {
            position: fixed;
            z-index: <?php echo absint( $zindex );?>;
            top: 0;
            left: 0;
            pointer-events: none;
        }
        .firework canvas {
            padding: 0;
            margin: 0;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            position: absolute;
            cursor: crosshair;
            display: block;
            z-index: <?php echo absint( $zindex );?>;
            pointer-events: none;
        }
        <?php endif; ?>
        <?php if ( ! $bottom_full ) : ?>
        .tet_bottom {
            position: fixed;
            bottom: 0;
            left: <?php echo absint( $bottom_left );?>px;
            z-index: <?php echo absint( $zindex );?>;
            width: <?php echo esc_attr( $bottom_w );?>;
            pointer-events: none;
        }
        <?php else : ?>
        .tet_bottom {
            position: fixed;
            bottom: 0;
            left: 0;
            z-index: <?php echo absint( $zindex );?>;
            width: 100%;
            height: 120px;
            background: url("<?php echo esc_url( devvn_tet_holiday_imgs( $bottom_style, 'url' ) );?>") repeat-x;
            background-size: auto 100% !important;
            pointer-events: none;
        }
        <?php endif; ?>
        @media (max-width: <?php echo absint( $container_width + $left_width );?>px){
            .tet_left, .tet_right, .tet_bottom{
                display: none !important;
            }
        }
    </style>
    <?php if ( 'hidden' !== $style ) : ?>
        <?php if ( $left_url ) : ?><div class="tet_left"><img src="<?php echo esc_url( $left_url );?>" alt=""/></div><?php endif; ?>
        <?php if ( $right_url ) : ?><div class="tet_right"><img src="<?php echo esc_url( $right_url );?>" alt=""/></div><?php endif; ?>
    <?php endif; ?>
    <?php if ( 'none' !== $bottom_style ) : ?>
        <div class="tet_bottom">
            <?php if ( ! $bottom_full ) : ?>
            <img src="<?php echo esc_url( devvn_tet_holiday_imgs( $bottom_style, 'url' ) );?>" alt=""/>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ( 1 === $enable_firework ) : ?>
        <script type="text/javascript">
            var boddie,bits=90,speed=<?php echo absint( devvn_get_tet_holiday_options( 'firework_speed' ) );?>,bangs=<?php echo absint( devvn_get_tet_holiday_options( 'firework_number' ) );?>,colours=new Array("#03f","#f03","#fff","#f7efa1","#0cf","#f93","#f0c","#fff"),bangheight=new Array,intensity=new Array,colour=new Array,Xpos=new Array,Ypos=new Array,dX=new Array,dY=new Array,stars=new Array,decay=new Array,swide=800,shigh=600;function write_fire(e){var t;for(stars[e+"r"]=createDiv("|",12),boddie.appendChild(stars[e+"r"]),t=bits*e;t<bits+bits*e;t++)stars[t]=createDiv("*",13),boddie.appendChild(stars[t])}function createDiv(e,t){var o=document.createElement("div");return o.style.font=t+"px monospace",o.style.position="absolute",o.style.backgroundColor="transparent",o.appendChild(document.createTextNode(e)),o}function launch(e){colour[e]=Math.floor(Math.random()*colours.length),Xpos[e+"r"]=.5*swide,Ypos[e+"r"]=shigh-5,bangheight[e]=Math.round((.5+Math.random())*shigh*.4),dX[e+"r"]=(Math.random()-.5)*swide/bangheight[e],1.25<dX[e+"r"]?stars[e+"r"].firstChild.nodeValue="/":dX[e+"r"]<-1.25?stars[e+"r"].firstChild.nodeValue="\\":stars[e+"r"].firstChild.nodeValue="|",stars[e+"r"].style.color=colours[colour[e]]}function bang(e){for(var t,o=0,n=bits*e;n<bits+bits*e;n++)(t=stars[n].style).left=Xpos[n]+"px",t.top=Ypos[n]+"px",decay[n]?decay[n]--:o++,15==decay[n]?t.fontSize="10px":7==decay[n]?t.fontSize="2px":1==decay[n]&&(t.visibility="hidden"),Xpos[n]+=dX[n],Ypos[n]+=dY[n]+=1.25/intensity[e];o!=bits&&setTimeout("bang("+e+")",speed)}function stepthrough(e){var t,o,n,i=Xpos[e+"r"],d=Ypos[e+"r"];if(Xpos[e+"r"]+=dX[e+"r"],Ypos[e+"r"]-=4,Ypos[e+"r"]<bangheight[e]){for(o=Math.floor(3*Math.random()*colours.length),intensity[e]=5+4*Math.random(),t=e*bits;t<bits+bits*e;t++)Xpos[t]=Xpos[e+"r"],Ypos[t]=Ypos[e+"r"],dY[t]=(Math.random()-.5)*intensity[e],dX[t]=(Math.random()-.5)*(intensity[e]-Math.abs(dY[t]))*1.25,decay[t]=25+Math.floor(25*Math.random()),n=stars[t],o<colours.length?n.style.color=colours[t%2?colour[e]:o]:o<2*colours.length?n.style.color=colours[colour[e]]:n.style.color=colours[t%colours.length],n.style.fontSize="20px",n.style.visibility="visible";bang(e),launch(e)}stars[e+"r"].style.left=i+"px",stars[e+"r"].style.top=d+"px"}function set_width(){var e=999999,t=999999;document.documentElement&&document.documentElement.clientWidth&&(0<document.documentElement.clientWidth&&(e=document.documentElement.clientWidth),0<document.documentElement.clientHeight&&(t=document.documentElement.clientHeight)),void 0!==self.innerWidth&&self.innerWidth&&(0<self.innerWidth&&self.innerWidth<e&&(e=self.innerWidth),0<self.innerHeight&&self.innerHeight<t&&(t=self.innerHeight)),document.body.clientWidth&&(0<document.body.clientWidth&&document.body.clientWidth<e&&(e=document.body.clientWidth),0<document.body.clientHeight&&document.body.clientHeight<t&&(t=document.body.clientHeight)),999999!=e&&999999!=t||(e=800,t=600),swide=e,shigh=t}window.onload=function(){var e;if(document.getElementById)for((boddie=document.createElement("div")).style.position="fixed",boddie.style.bottom="0px",boddie.style.top="0px",boddie.style.overflow="visible",boddie.classList.add("tet_firework"),boddie.style.width="0",boddie.style.height="0",boddie.style.backgroundColor="transparent",boddie.style.pointerEvents="none",document.body.appendChild(boddie),set_width(),e=0;e<bangs;e++)write_fire(e),launch(e),setInterval("stepthrough("+e+")",speed)},window.onresize=set_width;
            setTimeout(function() {
                document.body.removeChild(boddie);
            }, 10000);
        </script>
    <?php elseif ( 3 === $enable_firework ) : ?>
        <div class="firework"><canvas id="fire-work"></canvas></div>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                window.requestAnimFrame = function() {
                    return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || function(n) {
                        window.setTimeout(n, 1e3 / 60)
                    }
                }();
                function fireworksPlugin(n) {
                    // Hàm chuyển đổi mã hex sang giá trị HSL
                    function hexToHSL(hex) {
                        hex = hex.replace(/^#/, '');
                        let bigint = parseInt(hex, 16);
                        let r = (bigint >> 16) & 255;
                        let g = (bigint >> 8) & 255;
                        let b = bigint & 255;
                        r /= 255, g /= 255, b /= 255;
                        let max = Math.max(r, g, b), min = Math.min(r, g, b);
                        let h, s, l = (max + min) / 2;

                        if (max === min) {
                            h = s = 0; // achromatic
                        } else {
                            let d = max - min;
                            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                            switch (max) {
                                case r:
                                    h = (g - b) / d + (g < b ? 6 : 0);
                                    break;
                                case g:
                                    h = (b - r) / d + 2;
                                    break;
                                case b:
                                    h = (r - g) / d + 4;
                                    break;
                            }
                            h /= 6;
                        }

                        return { h: Math.round(h * 360), s: Math.round(s * 100), l: Math.round(l * 100) };
                    }
                    function r(n, t) {
                        return Math.random() * (t - n) + n
                    }
                    function w(n, t, i, r) {
                        var u = n - i
                            , f = t - r;
                        return Math.sqrt(Math.pow(u, 2) + Math.pow(f, 2))
                    }
                    function h(n, t, i, u) {
                        for (this.x = n,
                                 this.y = t,
                                 this.sx = n,
                                 this.sy = t,
                                 this.tx = i,
                                 this.ty = u,
                                 this.distanceToTarget = w(n, t, i, u),
                                 this.distanceTraveled = 0,
                                 this.coordinates = [],
                                 this.coordinateCount = 3; this.coordinateCount--; )
                            this.coordinates.push([this.x, this.y]);
                        this.angle = Math.atan2(u - t, i - n);
                        this.speed = 1.5;
                        this.acceleration = 1.03;
                        this.brightness = r(60, 70);
                        this.targetRadius = 1.5
                    }
                    function a(n, t) {
                        for (this.x = n,
                                 this.y = t,
                                 this.coordinates = [],
                                 this.coordinateCount = 5; this.coordinateCount--; )
                            this.coordinates.push([this.x, this.y]);
                        this.angle = r(0, Math.PI * 2);
                        this.speed = r(1, 10);
                        this.friction = .93;
                        this.gravity = 1;
                        this.hue = r(v - 15);
                        this.brightness = r(30, 80);
                        this.alpha = 2;
                        this.decay = r(.015, .07)
                    }
                    function g(n, t) {
                        for (var i = 80; i--; )
                            o.push(new a(n,t))
                    }
                    function b() {
                        var n, i;
                        for (requestAnimFrame(b),
                                 t.globalCompositeOperation = "destination-out",
                                 t.fillStyle = "rgba(0, 0, 0, 0.5)",
                                 t.fillRect(0, 0, f, e),
                                 t.globalCompositeOperation = "lighter",
                                 n = u.length; n--; )
                            u[n].draw(),
                                u[n].update(n);
                        for (i = o.length; i--; )
                            o[i].draw(),
                                o[i].update(i);
                        l >= d ? s || (u.push(new h(f / 2,e,r(0, f),r(0, e / 2))),
                            l = 0) : l++;
                        c >= k ? s && (u.push(new h(f / 2,e,y,p)),
                            c = 0) : c++
                    }
                    var i = n, t = i.getContext("2d"), f = window.innerWidth, e = window.innerHeight, u = [], o = [], v = hexToHSL("<?php echo esc_js( $firework_color );?>").h, k = 10, c = 0, d = <?php echo absint( $firework_speed_pc );?>, l = 0, s = !1, y, p;
                    if (matchMedia('only screen and (max-width: 550px)').matches) {
                        d = <?php echo absint( $firework_speed_mobile );?>;
                    }
                    i.width = f;
                    i.height = e;
                    h.prototype.update = function(n) {
                        this.coordinates.pop();
                        this.coordinates.unshift([this.x, this.y]);
                        this.targetRadius < 8 ? this.targetRadius += .3 : this.targetRadius = 1;
                        this.speed *= this.acceleration;
                        var t = Math.cos(this.angle) * this.speed
                            , i = Math.sin(this.angle) * this.speed;
                        this.distanceTraveled = w(this.sx, this.sy, this.x + t, this.y + i);
                        this.distanceTraveled >= this.distanceToTarget ? (g(this.tx, this.ty),
                            u.splice(n, 1)) : (this.x += t,
                            this.y += i)
                    };
                    h.prototype.draw = function() {
                        t.beginPath();
                        t.moveTo(this.coordinates[this.coordinates.length - 1][0], this.coordinates[this.coordinates.length - 1][1]);
                        t.lineTo(this.x, this.y);
                        t.strokeStyle = "hsl(" + v + ", 100%, " + this.brightness + "%)";
                        t.stroke();
                        t.beginPath();
                        t.arc(this.tx, this.ty, this.targetRadius, 0, Math.PI * 2);
                        t.stroke()
                    };
                    a.prototype.update = function(n) {
                        this.coordinates.pop();
                        this.coordinates.unshift([this.x, this.y]);
                        this.speed *= this.friction;
                        this.x += Math.cos(this.angle) * this.speed;
                        this.y += Math.sin(this.angle) * this.speed + this.gravity;
                        this.alpha -= this.decay;
                        this.alpha <= this.decay && o.splice(n, 1)
                    };
                    a.prototype.draw = function() {
                        t.beginPath();
                        t.moveTo(this.coordinates[this.coordinates.length - 1][0], this.coordinates[this.coordinates.length - 1][1]);
                        t.lineTo(this.x, this.y);
                        t.strokeStyle = "hsla(" + this.hue + ", 100%, " + this.brightness + "%, " + this.alpha + ")";
                        t.stroke()
                    };
                    i.addEventListener("mousemove", function(n) {
                        y = n.pageX - i.offsetLeft;
                        p = n.pageY - i.offsetTop
                    });
                    i.addEventListener("mousedown", function(n) {
                        n.preventDefault();
                        s = !0
                    });
                    i.addEventListener("mouseup", function(n) {
                        n.preventDefault();
                        s = !1
                    });
                    window.onload = b
                }
                i = document.getElementById("fire-work");
                fireworksPlugin(i);
                let countdown = <?php echo absint( $firework_timer );?>;
                let intervalId = setInterval(function() {
                    if (countdown <= 0) {
                        clearInterval(intervalId);
                        jQuery("#fire-work").hide();
                    }
                    countdown -= 1;
                }, 1000);
            });
        </script>
    <?php endif; ?>
    <?php if ( $enable_audio ) : ?>
        <iframe src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/phao-hoa.mp3' );?>" allow="autoplay" style="display:none" id="iframeAudio"></iframe>
        <audio autoplay id="playAudio">
            <source src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/phao-hoa.mp3' );?>" type="audio/mpeg">
        </audio>
        <script type="text/javascript">
            var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
            if (!isChrome){
                jQuery('#iframeAudio').remove()
            }
            else {
                jQuery('#playAudio').remove() // just to make sure that it will not have 2x audio in the background
            }
            let countdown_audio = <?php echo absint( $firework_timer );?>;
            let intervalAudioId = setInterval(function() {
                if (countdown_audio <= 0) {
                    clearInterval(intervalAudioId);
                    jQuery('#iframeAudio').remove();
                    jQuery('#playAudio').remove();
                }
                countdown_audio -= 1;
            }, <?php echo absint( $firework_timer * 1000 );?>);
        </script>
    <?php endif; ?>
    <?php if ( 'none' !== $enable_hoamaidao ) : ?>
    <script type="text/javascript">
        var no = <?php echo absint( devvn_get_tet_holiday_options( 'number_tet_pc' ) );?>;
        if (matchMedia('only screen and (max-width: 767px)').matches) {
            no = <?php echo absint( devvn_get_tet_holiday_options( 'number_tet_mobile' ) );?>;
        }
        let hoaArgs = [
            [
                '<?php echo esc_js( esc_url( devvn_tet_holiday_imgs( 'dao', 'url' ) ) );?>',
                <?php echo absint( devvn_tet_holiday_imgs( 'dao', 'w' ) );?>
            ],
            [
                '<?php echo esc_js( esc_url( devvn_tet_holiday_imgs( 'mai', 'url' ) ) );?>',
                <?php echo absint( devvn_tet_holiday_imgs( 'mai', 'w' ) );?>
            ],
        ];
        let img_url = '<?php echo esc_js( esc_url( devvn_tet_holiday_imgs( $enable_hoamaidao, 'url' ) ) );?>';
        let imgW = <?php echo absint( devvn_tet_holiday_imgs( $enable_hoamaidao, 'w' ) );?>;
        var hidesnowtime = 0;
        var color_snow  = '#fff';
        var snowdistance = 'windowheight'; // windowheight or pageheight;
        var ie4up = (document.all) ? 1 : 0;
        var ns6up = (document.getElementById && !document.all) ? 1 : 0;

        function iecompattest() {
            return (document.compatMode && document.compatMode != 'BackCompat') ? document.documentElement : document.body
        }

        var dx, xp, yp;
        var am, stx, sty;
        var i, doc_width = 800, doc_height = 600;
        if (ns6up) {
            doc_width = self.innerWidth;
            doc_height = self.innerHeight
        } else if (ie4up) {
            doc_width = iecompattest().clientWidth;
            doc_height = iecompattest().clientHeight
        }
        dx = new Array();
        xp = new Array();
        yp = new Array();
        am = new Array();
        stx = new Array();
        sty = new Array();
        for (i = 0; i < no; ++i) {
            dx[i] = 0;
            xp[i] = Math.random() * (doc_width - 50);
            yp[i] = Math.random() * doc_height;
            am[i] = Math.random() * 20;
            stx[i] = 0.02 + Math.random() / 10;
            sty[i] = 0.7 + Math.random();
            if (ie4up || ns6up) {
                <?php if ( 'both' === $enable_hoamaidao ) : ?>
                let hoaRandom = Math.floor(Math.random() * hoaArgs.length);
                img_url = hoaArgs[hoaRandom][0];
                imgW = hoaArgs[hoaRandom][1];
                <?php endif; ?>
                document.write('<div id="dot'+i+'" style="POSITION:fixed;Z-INDEX:'+(<?php echo absint( devvn_get_tet_holiday_options( 'zindex' ) );?>+i)+';VISIBILITY:visible;TOP:15px;LEFT:15px;pointer-events: none;width:'+imgW+'px"><span style="font-size:18px;color:'+color_snow+'"><img src="'+img_url+'" alt=""></span></div>');
            }
        }

        function snowIE_NS6() {
            doc_width = ns6up ? window.innerWidth - 10 : iecompattest().clientWidth - 10;
            doc_height = (window.innerHeight && snowdistance == 'windowheight') ? window.innerHeight : (ie4up && snowdistance == 'windowheight') ? iecompattest().clientHeight : (ie4up && !window.opera && snowdistance == 'pageheight') ? iecompattest().scrollHeight : iecompattest().offsetHeight;
            for (i = 0; i < no; ++i) {
                yp[i] += sty[i];
                if (yp[i] > doc_height - 50) {
                    xp[i] = Math.random() * (doc_width - am[i] - 30);
                    yp[i] = 0;
                    stx[i] = 0.02 + Math.random() / 10;
                    sty[i] = 0.7 + Math.random()
                }
                dx[i] += stx[i];
                document.getElementById('dot' + i).style.top = yp[i] + 'px';
                document.getElementById('dot' + i).style.left = xp[i] + am[i] * Math.sin(dx[i]) + 'px'
            }
            snowtimer = setTimeout('snowIE_NS6()', 10)
        }

        function hidesnow() {
            if (window.snowtimer) {
                clearTimeout(snowtimer)
            }
            for (i = 0; i < no; i++) document.getElementById('dot' + i).style.visibility = 'hidden'
        }

        if (ie4up || ns6up) {
            snowIE_NS6();
            if (hidesnowtime > 0) setTimeout('hidesnow()', hidesnowtime * 1000)
        }
    </script>
    <?php endif; ?>
    <?php
}

function devvn_tet_holiday_action_links( $links, $file ) {
    if ( strpos( $file, 'devvn-tet-holiday.php' ) !== false ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=setting-tet-holiday' ) ) . '" title="' . esc_attr__( 'Settings', 'devvn-tet-holiday' ) . '">' . esc_html__( 'Settings', 'devvn-tet-holiday' ) . '</a>';
        array_unshift( $links, $settings_link );
    }
    return $links;
}
add_filter( 'plugin_action_links_' . DEVVN_TET_HOLIDAY_BASENAME, 'devvn_tet_holiday_action_links', 10, 2 );


add_action( 'admin_init', 'devvn_tet_holiday_register_mysettings' );
function devvn_tet_holiday_register_mysettings() {
    register_setting( 'tet-options-group', 'tet_options', array(
        'sanitize_callback' => 'devvn_tet_holiday_sanitize_options',
    ) );
}

function devvn_tet_holiday_sanitize_options( $input ) {
    if ( ! is_array( $input ) ) {
        return array();
    }

    $sanitized = array();

    if ( isset( $input['style'] ) ) {
        $allowed_styles = array( 'hidden', 'style_1', 'style_2', 'style_3', 'style_4', 'style_5', 'style_6', 'style_7', 'custom' );
        $sanitized['style'] = in_array( $input['style'], $allowed_styles, true ) ? $input['style'] : 'style_1';
    }

    if ( isset( $input['bottom_style'] ) ) {
        $allowed_bottom_styles = array( 'none', 'bottom_1', 'bottom_2' );
        $sanitized['bottom_style'] = in_array( $input['bottom_style'], $allowed_bottom_styles, true ) ? $input['bottom_style'] : 'bottom_2';
    }

    if ( isset( $input['enable_firework'] ) ) {
        $sanitized['enable_firework'] = absint( $input['enable_firework'] );
    }

    if ( isset( $input['firework_speed'] ) ) {
        $sanitized['firework_speed'] = absint( $input['firework_speed'] );
    }

    if ( isset( $input['firework_number'] ) ) {
        $sanitized['firework_number'] = absint( $input['firework_number'] );
    }

    if ( isset( $input['firework_speed_pc'] ) ) {
        $sanitized['firework_speed_pc'] = absint( $input['firework_speed_pc'] );
    }

    if ( isset( $input['firework_speed_mobile'] ) ) {
        $sanitized['firework_speed_mobile'] = absint( $input['firework_speed_mobile'] );
    }

    if ( isset( $input['firework_timer'] ) ) {
        $sanitized['firework_timer'] = absint( $input['firework_timer'] );
    }

    if ( isset( $input['firework_color'] ) ) {
        $sanitized['firework_color'] = sanitize_hex_color( $input['firework_color'] );
    }

    if ( isset( $input['enable_hoamaidao'] ) ) {
        $allowed_hoa = array( 'none', 'dao', 'mai', 'both' );
        $sanitized['enable_hoamaidao'] = in_array( $input['enable_hoamaidao'], $allowed_hoa, true ) ? $input['enable_hoamaidao'] : 'dao';
    }

    if ( isset( $input['hoadao_custom'] ) ) {
        $sanitized['hoadao_custom'] = absint( $input['hoadao_custom'] );
    }

    if ( isset( $input['hoamai_custom'] ) ) {
        $sanitized['hoamai_custom'] = absint( $input['hoamai_custom'] );
    }

    if ( isset( $input['number_tet_mobile'] ) ) {
        $sanitized['number_tet_mobile'] = absint( $input['number_tet_mobile'] );
    }

    if ( isset( $input['number_tet_pc'] ) ) {
        $sanitized['number_tet_pc'] = absint( $input['number_tet_pc'] );
    }

    if ( isset( $input['enable_audio'] ) ) {
        $sanitized['enable_audio'] = absint( $input['enable_audio'] );
    }

    if ( isset( $input['container_width'] ) ) {
        $sanitized['container_width'] = absint( $input['container_width'] );
    }

    if ( isset( $input['zindex'] ) ) {
        $sanitized['zindex'] = absint( $input['zindex'] );
    }

    if ( isset( $input['left_banner'] ) ) {
        $sanitized['left_banner'] = absint( $input['left_banner'] );
    }

    if ( isset( $input['right_banner'] ) ) {
        $sanitized['right_banner'] = absint( $input['right_banner'] );
    }

    if ( isset( $input['show_mobile'] ) ) {
        $sanitized['show_mobile'] = absint( $input['show_mobile'] );
    }

    return $sanitized;
}

add_action( 'admin_menu', 'devvn_tet_holiday_admin_menu' );
function devvn_tet_holiday_admin_menu() {
    add_options_page(
        __('Trang trí Tết','devvn-tet-holiday'),
        __('Trang trí Tết','devvn-tet-holiday'),
        'manage_options',
        'setting-tet-holiday',
        'devvn_tet_holiday_settings_page'
    );
}

function devvn_tet_holiday_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'devvn-tet-holiday' ) );
    }

    $style = esc_attr( devvn_get_tet_holiday_options( 'style' ) );
    $bottom_style = esc_attr( devvn_get_tet_holiday_options( 'bottom_style' ) );
    $enable_firework = absint( devvn_get_tet_holiday_options( 'enable_firework' ) );
    $firework_speed = absint( devvn_get_tet_holiday_options( 'firework_speed' ) );
    $firework_number = absint( devvn_get_tet_holiday_options( 'firework_number' ) );
    $firework_timer = absint( devvn_get_tet_holiday_options( 'firework_timer' ) );
    $firework_speed_pc = absint( devvn_get_tet_holiday_options( 'firework_speed_pc' ) );
    $firework_speed_mobile = absint( devvn_get_tet_holiday_options( 'firework_speed_mobile' ) );
    $firework_color = sanitize_hex_color( devvn_get_tet_holiday_options( 'firework_color' ) );
    $enable_hoamaidao = esc_attr( devvn_get_tet_holiday_options( 'enable_hoamaidao' ) );
    $hoadao_custom = absint( devvn_get_tet_holiday_options( 'hoadao_custom' ) );
    $hoamai_custom = absint( devvn_get_tet_holiday_options( 'hoamai_custom' ) );
    $number_tet_mobile = absint( devvn_get_tet_holiday_options( 'number_tet_mobile' ) );
    $number_tet_pc = absint( devvn_get_tet_holiday_options( 'number_tet_pc' ) );
    $enable_audio = absint( devvn_get_tet_holiday_options( 'enable_audio' ) );
    wp_enqueue_media();
    $left_banner = absint( devvn_get_tet_holiday_options( 'left_banner' ) );
    $right_banner = absint( devvn_get_tet_holiday_options( 'right_banner' ) );
    ?>
    <style>
        .tet_style_radio input {
            border: 0;
            clip: rect(0 0 0 0);
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute;
            width: 1px;
        }
        .tet_style_radio img {
            box-shadow: 0 0 0 3px #ddd;
        }
        .tet_style_radio input:checked ~ img {
            box-shadow: 0 0 0 3px #cb0000;
        }
        .tet_style_radio label {
            float: left;
            margin: 0 20px 10px 0;
            cursor: pointer;
        }
        .tet_style_radio:after {
            content: "";
            display: table;
            clear: both;
        }
        .tet_style_radio_bottom img {
            height: 80px;
            width: auto;
        }
        .tet_style_radio label span {
            height: 80px;
            display: block;
            width: 80px;
            text-align: center;
            line-height: 80px;
            box-shadow: 0 0 0 3px #ddd;
        }
        .tet_style_radio_banner label span {
            height: 200px;
            line-height: 200px;
        }
        .tet_style_radio input:checked ~ span {
            box-shadow: 0 0 0 3px #cb0000;
        }
        tr.tet_border_top {
            border-top: 1px solid #ddd;
        }
        .tet-upload-image {
            width: 100%;
            max-width: 125px;
        }
        .tet-upload-image img{
            max-width: 100%;
            height: auto;
        }
        .view-has-value {
            display: none;
            position: relative;
        }

        .has-image .view-has-value {
            display: inline-block;
        }

        .hidden-has-value {
            display: block;
        }

        .has-image .hidden-has-value {
            display: none;
        }

        a.svl-delete-image {
            position: absolute;
            top: 0;
            right: -25px;
            color: #fff;
            background: #000;
            display: block;
            width: 20px;
            height: 20px;
            text-align: center;
            text-decoration: none;
        }
        .tet_hide, .hoa_hide{
            display: none !important;
        }
        .tet_style_radio.phao_hoa img {
            max-height: 80px;
        }
        .tet_style_radio.tet_style_radio_banner img {
            max-height: 200px;
        }
        .enable_firework_sh.hide{
            display: none !important;
        }
    </style>
    <div class="wrap">
        <h1><?php esc_html_e( 'Trang trí Tết', 'devvn-tet-holiday' ); ?></h1>
        <p>
            <strong style="color: red; font-size: 20px; font-style: italic">Chúc mừng năm mới!</strong>
        </p>
        <form method="post" action="options.php" novalidate="novalidate">
            <?php settings_fields( 'tet-options-group' );?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Chọn kiểu câu đối', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <div class="tet_style_radio tet_style_radio_banner">
                            <label>
                                <input type="radio" name="tet_options[style]" value="hidden" <?php checked( 'hidden', $style, true ); ?>>
                                <span><?php esc_html_e( 'Tắt câu đối', 'devvn-tet-holiday' ); ?></span>
                            </label>
                            <label>
                                <input type="radio" name="tet_options[style]" value="style_1" <?php checked( 'style_1', $style, true ); ?>>
                                <img src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/style-1.jpg' );?>" alt="">
                            </label>
                            <label>
                                <input type="radio" name="tet_options[style]" value="style_3" <?php checked( 'style_3', $style, true ); ?>>
                                <img src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/style-3.png' );?>" alt="">
                            </label>
                            <label>
                                <input type="radio" name="tet_options[style]" value="style_2" <?php checked( 'style_2', $style, true ); ?>>
                                <img src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/style-2.jpg' );?>" alt="">
                            </label>
                            <label>
                                <input type="radio" name="tet_options[style]" value="style_4" <?php checked( 'style_4', $style, true ); ?>>
                                <img src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/style-4.jpg' );?>" alt="">
                            </label>
                            <label>
                                <input type="radio" name="tet_options[style]" value="style_5" <?php checked( 'style_5', $style, true ); ?>>
                                <img src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/style-5.jpg' );?>" alt="">
                            </label>
                            <label>
                                <input type="radio" name="tet_options[style]" value="style_7" <?php checked( 'style_7', $style, true ); ?>>
                                <img src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/style-ngo.jpg' );?>" alt="">
                            </label>
                            <label>
                                <input type="radio" name="tet_options[style]" value="custom" <?php checked( 'custom', $style, true ); ?>>
                                <span><?php esc_html_e( 'Tuỳ chỉnh', 'devvn-tet-holiday' ); ?></span>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr class="tet_style_custom <?php echo ( 'custom' === $style ) ? '' : 'tet_hide'; ?>">
                    <th scope="row"><label><?php esc_html_e( 'Hình ảnh 2 bên', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <table>
                            <tr>
                                <td>
                                    <div class="tet-upload-image <?php if ( $left_banner ) : ?>has-image<?php endif; ?>">
                                        <div class="view-has-value">
                                            <input type="hidden" class="clone_delete" name="tet_options[left_banner]" id="maps_marker_icon" value="<?php echo esc_attr( $left_banner ); ?>"/>
                                            <img src="<?php echo esc_url( wp_get_attachment_image_url( $left_banner, 'full' ) ); ?>" class="image_view pins_img"/>
                                            <a href="#" class="svl-delete-image">x</a>
                                        </div>
                                        <div class="hidden-has-value"><input type="button" class="tet-upload button" value="<?php esc_attr_e( 'Chọn ảnh bên trái', 'devvn-tet-holiday' ); ?>" /></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="tet-upload-image <?php if ( $right_banner ) : ?>has-image<?php endif; ?>">
                                        <div class="view-has-value">
                                            <input type="hidden" class="clone_delete" name="tet_options[right_banner]" id="maps_marker_icon" value="<?php echo esc_attr( $right_banner ); ?>"/>
                                            <img src="<?php echo esc_url( wp_get_attachment_image_url( $right_banner, 'full' ) ); ?>" class="image_view pins_img"/>
                                            <a href="#" class="svl-delete-image">x</a>
                                        </div>
                                        <div class="hidden-has-value"><input type="button" class="tet-upload button" value="<?php esc_attr_e( 'Chọn ảnh bên phải', 'devvn-tet-holiday' ); ?>" /></div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Chọn kiểu chân trang', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <div class="tet_style_radio tet_style_radio_bottom">
                            <label>
                                <input type="radio" name="tet_options[bottom_style]" value="none" <?php checked( 'none', $bottom_style, true ); ?>>
                                <span><?php esc_html_e( 'Ẩn', 'devvn-tet-holiday' ); ?></span>
                            </label>
                            <label>
                                <input type="radio" name="tet_options[bottom_style]" value="bottom_1" <?php checked( 'bottom_1', $bottom_style, true ); ?>>
                                <img src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/bottom-1.png' ); ?>" alt="">
                            </label>
                            <label>
                                <input type="radio" name="tet_options[bottom_style]" value="bottom_2" <?php checked( 'bottom_2', $bottom_style, true ); ?>>
                                <img src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/bottom-2.png' ); ?>" alt="">
                            </label>
                        </div>
                    </td>
                </tr>
                <tr class="tet_border_top">
                    <th scope="row"><label><?php esc_html_e( 'Bật pháo hoa', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <div class="tet_style_radio phao_hoa">
                            <label style="margin-right: 20px;"><input type="radio" value="2" class="enable_firework_type" name="tet_options[enable_firework]" <?php checked( 2, $enable_firework ); ?>/> <span><?php esc_html_e( 'Tắt', 'devvn-tet-holiday' ); ?></span></label>
                            <label>
                                <input type="radio" value="1" class="enable_firework_type" name="tet_options[enable_firework]" <?php checked( 1, $enable_firework ); ?>/>
                                <img src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/phaohoa-type1.png' ); ?>" alt=""/>
                            </label>
                            <label>
                                <input type="radio" value="3" class="enable_firework_type" name="tet_options[enable_firework]" <?php checked( 3, $enable_firework ); ?>/>
                                <img src="<?php echo esc_url( DEVVN_TET_HOLIDAY_URL . 'images/phaohoa-type2.png' ); ?>" alt=""/>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr class="enable_firework_sh enable_firework_1">
                    <th scope="row"><label><?php esc_html_e( 'Tốc độ bắn pháo hoa', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <input type="number" min="1" value="<?php echo esc_attr( $firework_speed ); ?>" name="tet_options[firework_speed]" />
                        <br><small><?php esc_html_e( 'Số càng nhỏ thì bắn càng nhanh. Mặc định là 30', 'devvn-tet-holiday' ); ?></small>
                    </td>
                </tr>
                <tr class="enable_firework_sh enable_firework_1">
                    <th scope="row"><label><?php esc_html_e( 'Số pháo hoa bắn cùng lúc', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <input type="number" min="1" value="<?php echo esc_attr( $firework_number ); ?>" name="tet_options[firework_number]" />
                        <br><small><?php esc_html_e( 'Mặc định là 5', 'devvn-tet-holiday' ); ?></small>
                    </td>
                </tr>
                <tr class="enable_firework_sh enable_firework_3">
                    <th scope="row"><label><?php esc_html_e( 'Màu pháo hoa', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <input type="color" value="<?php echo esc_attr( $firework_color ); ?>" name="tet_options[firework_color]" />
                    </td>
                </tr>
                <tr class="enable_firework_sh enable_firework_3">
                    <th scope="row"><label><?php esc_html_e( 'Tốc độ bắn trên PC', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <input type="number" min="1" value="<?php echo esc_attr( $firework_speed_pc ); ?>" name="tet_options[firework_speed_pc]" />
                        <br><small><?php esc_html_e( 'Mặc định là 15. Số càng nhỏ càng nhanh', 'devvn-tet-holiday' ); ?></small>
                    </td>
                </tr>
                <tr class="enable_firework_sh enable_firework_3">
                    <th scope="row"><label><?php esc_html_e( 'Tốc độ bắn trên Mobile', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <input type="number" min="1" value="<?php echo esc_attr( $firework_speed_mobile ); ?>" name="tet_options[firework_speed_mobile]" />
                        <br><small><?php esc_html_e( 'Mặc định là 50. Số càng nhỏ càng nhanh', 'devvn-tet-holiday' ); ?></small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Âm thanh khi bắn pháo hoa', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <label style="margin-right: 20px;"><input type="radio" value="0" name="tet_options[enable_audio]" <?php checked( 0, $enable_audio, true ); ?>/> <?php esc_html_e( 'Tắt', 'devvn-tet-holiday' ); ?></label>
                        <label><input type="radio" value="1" name="tet_options[enable_audio]" <?php checked( 1, $enable_audio, true ); ?>/> <?php esc_html_e( 'Bật', 'devvn-tet-holiday' ); ?></label>
                        <br><small><?php esc_html_e( 'Có thể không chạy ở 1 số trình duyệt. Và có thể lần đầu không chạy nhưng khi chuyển trang sẽ chạy', 'devvn-tet-holiday' ); ?></small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Thời gian bắn pháo hoa', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <input type="number" step="1" min="0" value="<?php echo esc_attr( $firework_timer ); ?>" name="tet_options[firework_timer]" />s
                        <br><small><?php esc_html_e( 'Mặc định là 30s', 'devvn-tet-holiday' ); ?></small>
                    </td>
                </tr>
                <tr class="tet_border_top">
                    <th scope="row"><label><?php esc_html_e( 'Bật hoa rơi', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <label style="margin-right: 20px;"><input type="radio" value="none" name="tet_options[enable_hoamaidao]" <?php checked( 'none', $enable_hoamaidao, true ); ?>/> <?php esc_html_e( 'Tắt', 'devvn-tet-holiday' ); ?></label>
                        <label style="margin-right: 20px;"><input type="radio" value="dao" name="tet_options[enable_hoamaidao]" <?php checked( 'dao', $enable_hoamaidao, true ); ?>/> <?php esc_html_e( 'Hoa Đào', 'devvn-tet-holiday' ); ?> <img width="15" src="<?php echo esc_url( devvn_tet_holiday_imgs( 'dao', 'url' ) ); ?>" alt=""> </label>
                        <label style="margin-right: 20px;"><input type="radio" value="mai" name="tet_options[enable_hoamaidao]" <?php checked( 'mai', $enable_hoamaidao, true ); ?>/> <?php esc_html_e( 'Hoa Mai', 'devvn-tet-holiday' ); ?> <img width="15" src="<?php echo esc_url( devvn_tet_holiday_imgs( 'mai', 'url' ) ); ?>" alt=""></label>
                        <label style="margin-right: 20px;"><input type="radio" value="both" name="tet_options[enable_hoamaidao]" <?php checked( 'both', $enable_hoamaidao, true ); ?>/> <?php esc_html_e( 'Cả hoa đào', 'devvn-tet-holiday' ); ?> <img width="15" src="<?php echo esc_url( devvn_tet_holiday_imgs( 'dao', 'url' ) ); ?>" alt=""> <?php esc_html_e( 'và hoa mai', 'devvn-tet-holiday' ); ?> <img width="15" src="<?php echo esc_url( devvn_tet_holiday_imgs( 'mai', 'url' ) ); ?>" alt=""></label>
                    </td>
                </tr>
                <tr class="hoa_style_custom <?php echo ( 'none' !== $enable_hoamaidao ) ? '' : 'hoa_hide'; ?>">
                    <th scope="row"><label><?php esc_html_e( 'Hình ảnh hoa đào', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <div class="tet-upload-image <?php if ( $hoadao_custom ) : ?>has-image<?php endif; ?>">
                            <div class="view-has-value">
                                <input type="hidden" class="clone_delete" name="tet_options[hoadao_custom]" id="maps_marker_icon" value="<?php echo esc_attr( $hoadao_custom ); ?>"/>
                                <img src="<?php echo esc_url( wp_get_attachment_image_url( $hoadao_custom, 'full' ) ); ?>" class="image_view pins_img"/>
                                <a href="#" class="svl-delete-image">x</a>
                            </div>
                            <div class="hidden-has-value">
                                <input type="button" class="tet-upload button" value="<?php esc_attr_e( 'Chọn ảnh hoa đào', 'devvn-tet-holiday' ); ?>" />
                                <br><?php esc_html_e( 'Không bắt buộc. Mặc định là', 'devvn-tet-holiday' ); ?> <img width="15" src="<?php echo esc_url( devvn_tet_holiday_imgs( 'dao', 'url' ) ); ?>" alt="">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="hoa_style_custom <?php echo ( 'none' !== $enable_hoamaidao ) ? '' : 'hoa_hide'; ?>">
                    <th scope="row"><label><?php esc_html_e( 'Hình ảnh hoa mai', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <div class="tet-upload-image <?php if ( $hoamai_custom ) : ?>has-image<?php endif; ?>">
                            <div class="view-has-value">
                                <input type="hidden" class="clone_delete" name="tet_options[hoamai_custom]" id="maps_marker_icon" value="<?php echo esc_attr( $hoamai_custom ); ?>"/>
                                <img src="<?php echo esc_url( wp_get_attachment_image_url( $hoamai_custom, 'full' ) ); ?>" class="image_view pins_img"/>
                                <a href="#" class="svl-delete-image">x</a>
                            </div>
                            <div class="hidden-has-value">
                                <input type="button" class="tet-upload button" value="<?php esc_attr_e( 'Chọn ảnh hoa mai', 'devvn-tet-holiday' ); ?>" />
                                <br><?php esc_html_e( 'Không bắt buộc. Mặc định là', 'devvn-tet-holiday' ); ?> <img width="15" src="<?php echo esc_url( devvn_tet_holiday_imgs( 'mai', 'url' ) ); ?>" alt="">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Số hoa trên PC', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <input type="number" min="1" value="<?php echo esc_attr( $number_tet_pc ); ?>" name="tet_options[number_tet_pc]" />
                        <br><small><?php esc_html_e( 'Mặc định là 20', 'devvn-tet-holiday' ); ?></small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Số hoa trên Mobile', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <input type="number" min="1" value="<?php echo esc_attr( $number_tet_mobile ); ?>" name="tet_options[number_tet_mobile]" />
                        <br><small><?php esc_html_e( 'Mặc định là 10', 'devvn-tet-holiday' ); ?></small>
                    </td>
                </tr>
                <tr class="tet_border_top">
                    <th scope="row"><label><?php esc_html_e( 'Container Width', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <input type="number" min="0" value="<?php echo absint( devvn_get_tet_holiday_options( 'container_width' ) ); ?>" name="tet_options[container_width]" />
                        <br><small><?php esc_html_e( 'Chiều rộng website của bạn. Khi màn hình vượt qua số này + chiều rộng của mỗi kiểu thì sẽ ẩn các ảnh trang trí Tết đi', 'devvn-tet-holiday' ); ?></small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Z-index', 'devvn-tet-holiday' ); ?></label></th>
                    <td>
                        <input type="number" min="0" value="<?php echo absint( devvn_get_tet_holiday_options( 'zindex' ) ); ?>" name="tet_options[zindex]" />
                    </td>
                </tr>
                <?php do_settings_fields('tet-options-group', 'default'); ?>
                </tbody>
            </table>
            <?php do_settings_sections('tet-options-group', 'default'); ?>

            <?php submit_button();?>
        </form>
    </div>
    <script type="text/javascript">
        (function ($){
            $(document).ready(function (){
               $('input[name="tet_options[style]"]').on('change', function (){
                   let thisVal = $('input[name="tet_options[style]"]:checked').val();
                   if(thisVal == 'custom'){
                       $('.tet_style_custom').removeClass('tet_hide');
                   }else{
                       $('.tet_style_custom').addClass('tet_hide');
                   }
               });

               $('input[name="tet_options[enable_hoamaidao]"]').on('change', function (){
                   let thisVal = $('input[name="tet_options[enable_hoamaidao]"]:checked').val();
                   if(thisVal != 'none'){
                       $('.hoa_style_custom').removeClass('hoa_hide');
                   }else{
                       $('.hoa_style_custom').addClass('hoa_hide');
                   }
               });

                //image upload
                $('body').on('click','.tet-upload',function(e){
                    // Prevents the default action from occuring.
                    e.preventDefault();
                    var thisUpload = $(this).parents('.tet-upload-image');
                    // Sets up the media library frame
                    meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
                        title: 'Upload Image',
                        button: { text:  'Upload Image' },
                        library: { type: 'image' },
                        multiple: false
                    });
                    // Runs when an image is selected.
                    meta_image_frame.on('select', function(){
                        // Grabs the attachment selection and creates a JSON representation of the model.
                        var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
                        // Sends the attachment URL to our custom image input field.

                        if ( media_attachment.id ) {
                            var attachment_image = media_attachment.sizes && media_attachment.sizes.thumbnail ? media_attachment.sizes.thumbnail.url : media_attachment.url;

                            thisUpload.addClass('has-image');
                            thisUpload.find('input[type="hidden"]').val(media_attachment.id);
                            thisUpload.find('img.image_view').attr('src',media_attachment.url);
                        }
                    });
                    // Opens the media library frame.
                    meta_image_frame.open();
                });


                $('body').on('click','.svl-delete-image',function(){
                    var parentDiv = $(this).parents('.tet-upload-image');
                    parentDiv.removeClass('has-image');
                    parentDiv.find('input[type="hidden"]').val('');
                    return false;
                });

                function enable_firework_sh(){
                    let thisVal = $('.enable_firework_type:checked').val();
                    $('.enable_firework_sh').addClass('hide');
                    $('.enable_firework_'+thisVal).removeClass('hide');
                }
                $('body').on('change', '.enable_firework_type', function (){
                    enable_firework_sh();
                });
                enable_firework_sh();
            });
        })(jQuery);
    </script>
    <?php
}


function devvn_get_tet_holiday_options( $name = '' ) {
    $raw_options = get_option( 'tet_options', array() );
    $defaults = array(
        'zindex' => 99,
        'style' => 'style_7',
        'left_banner' => '',
        'right_banner' => '',
        'container_width' => 1140,
        'show_mobile' => 0,
        'bottom_style' => 'bottom_2',
        'enable_firework' => 1,
        'firework_speed' => 30,
        'firework_number' => 5,
        'firework_speed_pc' => 15,
        'firework_speed_mobile' => 50,
        'firework_timer' => 30,
        'enable_hoamaidao' => 'dao',
        'hoadao_custom' => '',
        'hoamai_custom' => '',
        'number_tet_mobile' => 10,
        'number_tet_pc' => 20,
        'enable_audio' => 0,
        'firework_color' => '#FF0000',
    );
    $options = wp_parse_args( $raw_options, $defaults );

    $options = devvn_tet_holiday_sanitize_options_on_load( $options );

    if ( $name ) {
        return isset( $options[ $name ] ) ? $options[ $name ] : '';
    }
    return $options;
}

function devvn_tet_holiday_sanitize_options_on_load( $options ) {
    if ( ! is_array( $options ) ) {
        return array();
    }

    $sanitized = array();

    if ( isset( $options['style'] ) ) {
        $allowed_styles = array( 'hidden', 'style_1', 'style_2', 'style_3', 'style_4', 'style_5', 'style_6', 'style_7', 'custom' );
        $sanitized['style'] = in_array( $options['style'], $allowed_styles, true ) ? sanitize_text_field( $options['style'] ) : 'style_1';
    }

    if ( isset( $options['bottom_style'] ) ) {
        $allowed_bottom_styles = array( 'none', 'bottom_1', 'bottom_2' );
        $sanitized['bottom_style'] = in_array( $options['bottom_style'], $allowed_bottom_styles, true ) ? sanitize_text_field( $options['bottom_style'] ) : 'bottom_2';
    }

    if ( isset( $options['enable_firework'] ) ) {
        $sanitized['enable_firework'] = absint( $options['enable_firework'] );
    }

    if ( isset( $options['firework_speed'] ) ) {
        $sanitized['firework_speed'] = absint( $options['firework_speed'] );
    }

    if ( isset( $options['firework_number'] ) ) {
        $sanitized['firework_number'] = absint( $options['firework_number'] );
    }

    if ( isset( $options['firework_speed_pc'] ) ) {
        $sanitized['firework_speed_pc'] = absint( $options['firework_speed_pc'] );
    }

    if ( isset( $options['firework_speed_mobile'] ) ) {
        $sanitized['firework_speed_mobile'] = absint( $options['firework_speed_mobile'] );
    }

    if ( isset( $options['firework_timer'] ) ) {
        $sanitized['firework_timer'] = absint( $options['firework_timer'] );
    }

    if ( isset( $options['firework_color'] ) ) {
        $sanitized['firework_color'] = sanitize_hex_color( $options['firework_color'] );
    }

    if ( isset( $options['enable_hoamaidao'] ) ) {
        $allowed_hoa = array( 'none', 'dao', 'mai', 'both' );
        $sanitized['enable_hoamaidao'] = in_array( $options['enable_hoamaidao'], $allowed_hoa, true ) ? sanitize_text_field( $options['enable_hoamaidao'] ) : 'dao';
    }

    if ( isset( $options['hoadao_custom'] ) ) {
        $sanitized['hoadao_custom'] = absint( $options['hoadao_custom'] );
    }

    if ( isset( $options['hoamai_custom'] ) ) {
        $sanitized['hoamai_custom'] = absint( $options['hoamai_custom'] );
    }

    if ( isset( $options['number_tet_mobile'] ) ) {
        $sanitized['number_tet_mobile'] = absint( $options['number_tet_mobile'] );
    }

    if ( isset( $options['number_tet_pc'] ) ) {
        $sanitized['number_tet_pc'] = absint( $options['number_tet_pc'] );
    }

    if ( isset( $options['enable_audio'] ) ) {
        $sanitized['enable_audio'] = absint( $options['enable_audio'] );
    }

    if ( isset( $options['container_width'] ) ) {
        $sanitized['container_width'] = absint( $options['container_width'] );
    }

    if ( isset( $options['zindex'] ) ) {
        $sanitized['zindex'] = absint( $options['zindex'] );
    }

    if ( isset( $options['left_banner'] ) ) {
        $sanitized['left_banner'] = absint( $options['left_banner'] );
    }

    if ( isset( $options['right_banner'] ) ) {
        $sanitized['right_banner'] = absint( $options['right_banner'] );
    }

    if ( isset( $options['show_mobile'] ) ) {
        $sanitized['show_mobile'] = absint( $options['show_mobile'] );
    }

    return wp_parse_args( $sanitized, $options );
}

add_filter( 'devvn_tet_holiday_imgs', function ( $args ) {
    $hoadao = absint( devvn_get_tet_holiday_options( 'hoadao_custom' ) );
    if ( $hoadao ) {
        $image = wp_get_attachment_image_src( $hoadao, 'full' );
        if ( $image && isset( $image[0] ) && isset( $image[1] ) ) {
            list( $src, $width, $height ) = $image;
            $args['dao']['url'] = esc_url_raw( $src );
            $args['dao']['w'] = absint( $width );
        }
    }

    $hoamai = absint( devvn_get_tet_holiday_options( 'hoamai_custom' ) );
    if ( $hoamai ) {
        $image = wp_get_attachment_image_src( $hoamai, 'full' );
        if ( $image && isset( $image[0] ) && isset( $image[1] ) ) {
            list( $src, $width, $height ) = $image;
            $args['mai']['url'] = esc_url_raw( $src );
            $args['mai']['w'] = absint( $width );
        }
    }
    return $args;
} );