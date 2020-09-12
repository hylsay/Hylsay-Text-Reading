<?php
/*
Plugin Name: Hylsay Text Reading
Plugin URI: https://aoaoao.info/hylsay-text-reading
Description: A plug-in that can read
Version: 1.1.0
Author: hylsay
Author URI: http://aoaoao.info
*/

function hylsay_text_reading_admin_mycss() {
    echo '<style type="text/css">
    .form-table th {
		font-weight:400;
	}
    </style>';
 }
add_action('admin_head', 'hylsay_text_reading_admin_mycss');

class HylsayTextReadingPlugin {
	private $baiduaudio_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'baiduaudio_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'baiduaudio_page_init' ) );
	}

	public function baiduaudio_plugin_page() {
		add_menu_page(
			'文章阅读插件', // page_title
			'文章阅读插件', // menu_title
			'manage_options', // capability
			'baiduaudio', // menu_slug
			array( $this, 'baiduaudio_create_admin_page' ), // function
			'dashicons-admin-generic', // icon_url
			100 // position
		);
	}

	public function baiduaudio_create_admin_page() {
		$this->baiduaudio_options = get_option( 'baiduaudio_option_name' ); ?>

		<div class="wrap">
			<h2>文章阅读插件设置</h2>
			<p><b>插件介绍：</b></p>
			<p>本插件是基于百度语音合成开发，需要自行申请百度语音合成APIkey，地址：<a href="http://ai.baidu.com/tech/speech/" target="_blank">http://ai.baidu.com/tech/speech/</a></p>
			<p><b>插件设置：</b></p>
			<p>1.阅读范围。默认是article，如果不想阅读标题等信息，请填写正文div标签，例如：.entry-content。</p>
			<p>2.阅读屏蔽。默认是#baiduAudioPlayer,iframe,[anti],[copy],pre,img,table,.modal等，如果不想阅读某个div中的内容，就把该div对应的标签填写进去，例如：.ads-google,.announce，如果需要屏蔽的较多切记使用逗号（英文半角）分开。</p>
            <?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'baiduaudio_option_group' );
					do_settings_sections( 'baiduaudio-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function baiduaudio_page_init() {
		register_setting(
			'baiduaudio_option_group', // option_group
			'baiduaudio_option_name', // option_name
			array( $this, 'baiduaudio_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'baiduaudio_setting_section', // id
			'基础设置', // title
			array( $this, 'baiduaudio_section_info' ), // callback
			'baiduaudio-admin' // page
		);
		

		add_settings_field(
			'baidu_apiKey', // id
			'百度语音合成apiKey', // title
			array( $this, 'baidu_apiKey_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baidu_secretKey', // id
			'百度语音合成secretKey', // title
			array( $this, 'baidu_secretKey_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'select_shengyin', // id
			'选择声音类型', // title
			array( $this, 'select_shengyin_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baiduaudio_post_divtag', // id
			'阅读范围', // title
			array( $this, 'baiduaudio_post_divtag_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baiduaudio_pingbi_divtag', // id
			'阅读屏蔽', // title
			array( $this, 'baiduaudio_pingbi_divtag_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);
	
	}

	public function baiduaudio_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['baidu_apiKey'] ) ) {
			$sanitary_values['baidu_apiKey'] = sanitize_text_field( $input['baidu_apiKey'] );
		}

		if ( isset( $input['baidu_secretKey'] ) ) {
			$sanitary_values['baidu_secretKey'] = sanitize_text_field( $input['baidu_secretKey'] );
		}

		if ( isset( $input['select_shengyin'] ) ) {
			$sanitary_values['select_shengyin'] = $input['select_shengyin'];
		}

		if ( isset( $input['baiduaudio_post_divtag'] ) ) {
			$sanitary_values['baiduaudio_post_divtag'] = sanitize_text_field( $input['baiduaudio_post_divtag'] );
		}

		if ( isset( $input['baiduaudio_pingbi_divtag'] ) ) {
			$sanitary_values['baiduaudio_pingbi_divtag'] = sanitize_text_field( $input['baiduaudio_pingbi_divtag'] );
		}

		return $sanitary_values;
	}

	public function baiduaudio_section_info() {
		echo '<hr>';
	}


	public function select_shengyin_callback() {
		?> <fieldset>
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '0' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-0"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-0" value="0" checked > 标准女声</label><br>
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '1' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-1"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-1" value="1" <?php echo $checked; ?>> 标准男声</label><br>
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '4' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-3"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-3" value="4" <?php echo $checked; ?>> 情感女声</label><br>
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '3' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-2"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-2" value="3" <?php echo $checked; ?>> 情感男声</label>
		
		</fieldset> <?php
	}

	public function baidu_apiKey_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_apiKey]" id="baidu_apiKey" value="%s">',
			isset( $this->baiduaudio_options['baidu_apiKey'] ) ? esc_attr( $this->baiduaudio_options['baidu_apiKey']) : ''
		);
	}

	public function baidu_secretKey_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_secretKey]" id="baidu_secretKey" value="%s">',
			isset( $this->baiduaudio_options['baidu_secretKey'] ) ? esc_attr( $this->baiduaudio_options['baidu_secretKey']) : ''
		);
	}

	public function baiduaudio_post_divtag_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baiduaudio_post_divtag]" id="baiduaudio_post_divtag" value="%s" placeholder="例如：.entry-content">',
			isset( $this->baiduaudio_options['baiduaudio_post_divtag'] ) ? esc_attr( $this->baiduaudio_options['baiduaudio_post_divtag']) : ''
		);
	}

	public function baiduaudio_pingbi_divtag_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baiduaudio_pingbi_divtag]" id="baiduaudio_pingbi_divtag" value="%s" placeholder="例如：.ads-google，自定义div标签等">',
			isset( $this->baiduaudio_options['baiduaudio_pingbi_divtag'] ) ? esc_attr( $this->baiduaudio_options['baiduaudio_pingbi_divtag']) : ''
		);
	}


}
if ( is_admin() )
	$baiduaudio = new HylsayTextReadingPlugin();


function add_hylsay_text_reading_js($hook) {
 
    // create my own version codes
    $my_js_ver  = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'baiduAudio.js' ));
    $my_css_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'baiduAudio.css' ));
     
    // 
    wp_enqueue_script( 'baiduAudio_js', plugins_url( 'baiduAudio.js', __FILE__ ), array(), $my_js_ver );
    wp_register_style( 'baiduAudio_css',    plugins_url( 'baiduAudio.css',    __FILE__ ), false,   $my_css_ver );
    wp_enqueue_style ( 'baiduAudio_css' );
 
}
add_action('wp_enqueue_scripts', 'add_hylsay_text_reading_js');

//注册一个路由
add_action('rest_api_init', function () {
	register_rest_route('hylsaytextreading', '/hylsay_text_reading_get_baiduAudio_token/', array('methods' => 'get', 'callback' => 'hylsay_text_reading_get_baiduAudio_token',));
});

function hylsay_text_reading_get_cache($name) {
    $allCache = get_option('pd_cache');
    if (!$allCache) {
        return false;
    }
    if (!$allCache[$name]) {
        return false;
    } else {
        $time = $allCache[$name]['expire'];
        if ($time > time() & $time - time() < 2592000) {
            return $allCache[$name]['data'];;
        } else {
            del_cache($name);
            return false;
        }
    }
}

function hylsay_text_reading_set_cache($name, $data, $expire) {
    $allCache = get_option('pd_cache');
    if (!$allCache) {
        $allCache = array();
    }
    $allCache[$name] = array('data' => $data, 'expire' => time() + $expire);
    update_option('pd_cache', $allCache);
}

function hylsay_text_reading_get_baiduAudio_token() {
	$result = hylsay_text_reading_get_cache('baidu_Audio_token');
	$baiduaudio_options = get_option( 'baiduaudio_option_name' );
	if ($result == false) {
		$apiKey = $baiduaudio_options['baidu_apiKey'];
		$secretKey = $baiduaudio_options['baidu_secretKey'];
		$api = 'https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=' . $apiKey . '&client_secret=' . $secretKey;
		$api = wp_remote_get( $api );
		$result = wp_remote_retrieve_body( $response );
		if ($result['access_token']) {
			hylsay_text_reading_set_cache('baidu_Audio_token', $result, $result[expires_in] * 0.9);
		}
	}
	
	$getper = $baiduaudio_options['select_shengyin'];
	$getposttag = $baiduaudio_options['baiduaudio_post_divtag'];
	$getpingbitag = $baiduaudio_options['baiduaudio_pingbi_divtag'];
	$return = array('access_token' => $result[access_token], 'session_key' => $result[session_key], 'spd' =>  5, 'pit' =>  5, 'per' =>  $getper,'yuedu_posttag' => $getposttag,'yuedu_pingbitag' => $getpingbitag);
	return $return;
}

function hylsay_text_reading_baidu_ai_audio_content($content) {
	
	if (is_single()) {
		return '<div class="baiduAudioWrap"></div>' . $content;
	}
	return $content;
}
add_filter("the_content", "hylsay_text_reading_baidu_ai_audio_content");

