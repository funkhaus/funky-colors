<pre style="padding: 20px;">
Image status is saved for each attachment as the meta key "fgram_status" and has three possible values:
	1. "draft"
	2. "pending_review"
	3. "published"

When looping through attachments, query for images in the published state like so:
<strong>
$args = array(
	"posts_per_page" => -1,
	"meta_key" => "fgram_status",
	"meta_value" => "published",
	"post_type" => "attachment",
	"post_parent" => $post->ID
);
$images = get_posts($args);
</strong>
</pre>
