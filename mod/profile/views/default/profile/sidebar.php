<?php
/**
 * Elgg profile icon edit form
 *
 * @package ElggProfile
 */
?>
<div id="profile_sidebar">
<?php
	$section = $vars['section'];
	echo elgg_view('profile/profile_ownerblock', array('section' => $section));
?>
</div>