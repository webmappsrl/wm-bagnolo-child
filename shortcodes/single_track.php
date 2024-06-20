<?php
if (!is_admin()) {
	add_shortcode('wm_single_track', 'wm_single_track');
}

function wm_single_track($atts)
{
	if (defined('ICL_LANGUAGE_CODE')) {
		$language = ICL_LANGUAGE_CODE;
	} else {
		$language = 'it';
	}
	extract(shortcode_atts(array(
		'track_id' => '',
	), $atts));

	$single_track_base_url = get_option('track_url');
	$geojson_url = $single_track_base_url . $track_id;

	$track = json_decode(file_get_contents($geojson_url), true);
	$track = $track['properties'];
	$iframeUrl = "https://geohub.webmapp.it/w/simple/" . $track_id;

	$description = null;
	$excerpt = null;
	$title = null;
	$featured_image = null;
	$gallery = [];
	$gpx = null;
	$activity = null;

	if ($track) {
		$description = $track['description'][$language] ?? null;
		$excerpt = $track['excerpt'][$language] ?? null;
		$title = $track['name'][$language] ?? null;
		$featured_image_url = $track['feature_image']['url'] ?? get_stylesheet_directory_uri() . '/assets/images/feature_image.jpg';
		$featured_image = $track['feature_image']['sizes']['1440x500'] ?? $featured_image_url;
		$gallery = $track['image_gallery'] ?? [];
		$gpx = $track['gpx_url'] ?? null;
		$activity = $track['taxonomy']['activity'] ?? [];
	}
	ob_start();
?>

	<section class="l-section wpb_row height_small with_img with_overlay wm_header_section">
		<div class="l-section-img loaded wm-header-image" style="background-image: url(<?= $featured_image ?>);background-repeat: no-repeat;">
		</div>
		<div class="l-section-h i-cf wm_header_wrapper">
		</div>
	</section>

	<div class="wm_body_track_section">
		<div class="wm_body_map_wrapper">
			<?php if ($title) { ?>
				<h1 class="align_left wm_header_title">
					<?= $title ?>
				</h1>
			<?php } ?>
			<?php if (!empty($activity)) : ?>
				<div class="wm_activities">
					<?php foreach ($activity as $type) : ?>
						<span class="wm_activity">
							<?php if (!empty($type['icon'])) : ?>
								<span class="wm_activity_icon"><?= $type['icon'] ?></span>
							<?php endif; ?>
							<span class="wm_activity_name"><?= esc_html($type['name'][$language] ?? 'N/A') ?></span>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<iframe class="wm_iframe_map_track" src="<?= esc_url($iframeUrl); ?>" loading="lazy"></iframe>
			<div class="wm_track_body_download">
			</div>

			<?php if ($description) { ?>
				<div class="wm_body_description">
					<?php echo $description; ?>
				</div>
			<?php } ?>

			<div class="wm_body_gallery">
				<?php if (is_array($gallery) && !empty($gallery)) : ?>
					<div class="swiper-container">
						<div class="swiper-wrapper">
							<?php foreach ($gallery as $image) : ?>
								<div class="swiper-slide">
									<?php
									$thumbnail_url = isset($image['thumbnail']) ? esc_url($image['thumbnail']) : '';
									$high_res_url = isset($image['url']) ? esc_url($image['url']) : $thumbnail_url;
									$caption = isset($image['caption'][$language]) ? esc_attr($image['caption'][$language]) : '';
									if ($thumbnail_url) : ?>
										<a href="<?= $high_res_url ?>" data-lightbox="track-gallery" data-title="<?= $caption ?>">
											<img src="<?= $thumbnail_url ?>" alt="<?= $caption ?>" loading="lazy">
										</a>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
						<div class="swiper-pagination"></div>
						<div class="swiper-button-prev"></div>
						<div class="swiper-button-next"></div>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<script>
			document.addEventListener('DOMContentLoaded', function() {
				var swiper = new Swiper('.swiper-container', {
					slidesPerView: 1,
					spaceBetween: 10,
					breakpoints: {
						768: {
							slidesPerView: 3,
							spaceBetween: 5
						},
					},
					freeMode: true,
					loop: true,
					pagination: {
						el: '.swiper-pagination',
						clickable: true,
					},
					navigation: {
						nextEl: '.swiper-button-next',
						prevEl: '.swiper-button-prev',
					},
				});
			});
		</script>
	</div>

<?php
	return ob_get_clean();
}
?>