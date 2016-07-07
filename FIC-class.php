<?php

	class Funkstagram {

		public $access_token;
		public $page_id;
		public $user_ids;
		public $tags;

		private $error_log;

		/*
		 * Helper function used to gather instagram user IDs
		 * @Returns array of user IDs to get photos from, or false if no IDs
		 *
		 * @Todo once user ID is fetched through api, cache as an option to save calls
		 */
			private function get_user_ids() {

				if ( empty($this->user_ids) ) return false;

				// Convert string of Instagram IDs into array
				$user_ids = trim( preg_replace('/\s+/','', $this->user_ids) );
				$user_ids = explode(',', $user_ids);
				$user_ids = array_filter($user_ids);

				foreach ( $user_ids as $i => $user ) {

					// Call api for this user
					$check_id = wp_remote_retrieve_response_code( wp_remote_get('https://api.instagram.com/v1/users/' . $user . '/?access_token=' . $this->access_token ) );

					// if successful, do not change array value
					if ( $check_id == 200 ) continue;

					// ID failed, search api for this user by name
					$serach_user = wp_remote_get('https://api.instagram.com/v1/users/search?q=' . $user . '&access_token=' . $this->access_token );

					// If user search is successful...
					if ( wp_remote_retrieve_response_code( $serach_user ) == 200 ) {

						// Decode JSON response
						$response = json_decode( wp_remote_retrieve_body( $serach_user ), true );

                        			// Go through serach results and find matching username
                        			$found_id = false;
                        			foreach($response['data'] as $key => $found_user) {
                            				if(strtolower($found_user['username']) == strtolower($user)) {
                                				$found_id = $response['data'][$key]['id'];
                                				break;
                            				}
                        			}

                        			// If username matches, switch username to be id instead
                        			if ( $found_id ) {
                            				$user_ids[$i] = $found_id;

                        			// Otherwise remove from array and log
                        			} else {
                            				unset( $user_ids[$i] );
                            				$this->error_log[] = 'User ' . $user . ' does not exist';
                        			}

					// No response, remove from array and log
					} else {
						unset( $user_ids[$i] );
						$this->error_log[] = 'User ' . $user . ' does not exist';
					}

				}

				// Return all unique users
				return array_unique( $user_ids );

			}

		/*
		 * Helper function used to gather instagram posts
		 * @Returns date-sorted object of all posts from all users
		 */
			private function gatherObjects() {

				$user_ids = $this->get_user_ids();

				// Check for API key and log error accordingly
				if ( empty($this->access_token) ) {
					$this->error_log[] = 'No access token is set';
				}

				// Set post container
				$all_posts = array();
				if ( $user_ids ) {
					$all_posts = $this->fetch_by_user( $user_ids );
				} elseif ( !empty( $this->tags ) ) {
					$all_posts = $this->fetch_by_tag();
				} else {
					$this->error_log[] = 'Must set at least 1 user or tag';
				}

				// Sort all posts from oldest to newest
				usort($all_posts, function($a, $b) {
				    return $a['created_time'] - $b['created_time'];
				});

				return $all_posts;

			}

		/*
		 * Helper function used to gather posts through API by id
		 * @Param ARRAY: array of user IDs to fetch posts for
		 * @Returns date-sorted object of all posts from all users
		 */
			private function fetch_by_user( $user_ids ) {

				// Init output
				$output = array();

				// Loop through IDs
				foreach ( $user_ids as $user_id ) {

					// Pull json from api and decode
					$url = 'https://api.instagram.com/v1/users/' . $user_id . '/media/recent/?access_token=' . $this->access_token;

					// Get contents of URL and if successful...
					$response = wp_remote_retrieve_body( wp_remote_get($url) );

					if ( $response ) {

						// Decode json into array
						$response = json_decode($response, true);

						// Make sure data is formatted properly
						if ( is_array($response["data"]) ) {

							foreach ($response["data"] as $single_post) {

								// Push each post into all_posts array
								array_push($output, $single_post);

							}

						} else {
							$this->error_log[] = 'Server error, failed to get content from Instagram api for user: ' . $user_id;
							continue;
						}

					} else {

						// If unsuccessful, log it
						$this->error_log[] = 'Server error, failed to get content from Instagram api for user: ' . $user_id;
						continue;

					}
				}

				return $output;

			}

		/*
		 * Helper function used to gather posts through API by tag
		 * $Param ARRAY: set of tags to get posts for
		 * @Returns date-sorted object of all posts from all users
		 */
			private function fetch_by_tag( $tags = false ) {

				if ( ! $tags ) $tags = explode(',', $this->tags);

				$output = array();

				// Loop through IDs
				foreach ( $tags as $tag ) {

					// Pull json from api and decode
					$url = 'https://api.instagram.com/v1/tags/' . $tag . '/media/recent?access_token=' . $this->access_token;

					// Get contents of URL and if successful...
					$response = wp_remote_retrieve_body( wp_remote_get($url) );

					if ( $response ) {

						// Decode json into array
						$response = json_decode($response, true);

						// Make sure data is formatted properly
						if ( is_array($response["data"]) ) {

							foreach ($response["data"] as $single_post) {

								// Push each post into all_posts array
								array_push($output, $single_post);

							}

						} else {
							$this->error_log[] = 'Server error, failed to get content from Instagram api for tag: ' . $tag;
							continue;
						}

					} else {

						// If unsuccessful, log it
						$this->error_log[] = 'Server error, failed to get content from Instagram api for tag: ' . $tag;
						continue;

					}
				}

				return $output;

			}

		/*
		 * The following code has been taken from
		 * the media_handle_sideload() reference page
		 * http://codex.wordpress.org/Function_Reference/media_handle_sideload
		 *
		 * Sets URL into a useable $_FILE array, and attaches it to page
		 * @Returns image ID on success, or false on failure
		 *
		 */
			private function sideLoad($url) {

				if ( ! function_exists('download_url') ) {
					require_once('wp-admin/includes/image.php');
					require_once('wp-admin/includes/file.php');
					require_once('wp-admin/includes/media.php');
				}

				//download image from url
				$tmp = download_url( $url );

				// Set variables for storage
				// fix file filename for query strings
				preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);
				$file_array['name'] = basename($matches[0]);
				$file_array['tmp_name'] = $tmp;

				// If error storing temporarily, unlink
				if ( is_wp_error( $tmp ) ) {
					@unlink($file_array['tmp_name']);
					$file_array['tmp_name'] = '';
				}

				// do the validation and storage stuff,
				// Set ID
				$id = media_handle_sideload( $file_array, $this->page_id );

				// If error storing permanently, unlink and return 0
				if ( is_wp_error($id) ) {
					@unlink($file_array['tmp_name']);
					return false;
				}

				return $id;
			}

		/*
		 * Main function used to import feeds and attach them to page
		 */
			public function import() {

				// Get all gathered posts
				$gram_posts = $this->gatherObjects();

				if ( empty($this->page_id) ) {
					$this->error_log[] = 'No attachment page is set';
				}

				$uploadCount = 0;
				// Loop through each post
				foreach ( $gram_posts as $gram_post ) {

					// Check for any existing attachments with this post's instagram ID
					$args = array(
						'posts_per_page'	=> 1,
						'meta_key'			=> 'instagram_id',
						'meta_value'		=> $gram_post["id"],
						'post_type'			=> 'attachment'
					);
					$existing_posts = get_posts($args);

					// If any matching attachments are found,
					// then skip this post, because it already exists
					if ($existing_posts) {
						continue;
					}

					// If any filter tags are set...
					if ( $this->tags ) {

						// Check if this post has tags,
						// if not skip it
						if ( empty($gram_post['tags']) )  {
							continue;
						}

						// Set list of filter tags
						$whitelist = explode(',', $this->tags);
						$load_post = false;

						// Loop through post tags
						foreach ( $gram_post['tags'] as $tag ) {

							// If any tag is in whitelist, set to true
							if ( in_array( $tag, $whitelist ) ) {
								$load_post = true;
							}

						}

						// if no tags matched, skip this post
						if ( ! $load_post ) {
							continue;
						}

					}

					// Attempt to sideload (import) the image
					$attachment_id = $this->sideLoad( $gram_post["images"]["standard_resolution"]["url"]);

					// If attached...
					if ( $attachment_id ) {

						// Increment counter
						$uploadCount++;

						// Add instagram ID, video URl, and user name meta fields
						add_post_meta($attachment_id, 'instagram_id', $gram_post["id"]);
						if ( isset( $gram_post["videos"] ) ) {
							add_post_meta($attachment_id, 'instagram_video_url', $gram_post["videos"]["standard_resolution"]["url"]);
						}
						add_post_meta($attachment_id, 'instagram_user', $gram_post["user"]["full_name"]);
						add_post_meta($attachment_id, 'instagram_alldata', base64_encode(serialize($gram_post)), true);

						// Load attachment by ID and set caption
						$this_attachment = get_post($attachment_id);
						$this_attachment->post_content = sanitize_text_field( $gram_post["caption"]["text"] ); // adjust this decode to handle emoticons
						wp_update_post( $this_attachment );

					} else {

						// If sideload is unsuccessful, log it
						$this->error_log[] = 'Image attachment was unsuccessful for image ID: ' . $gram_post["id"];
						continue;

					}

				} //End loop

				// Output any/all errors logged along the way
				// @Todo fix this formatting hack
				$total_errors = count($this->error_log);
				$output = '<br><br><strong>' . $total_errors . ' errors returned:<br>';
				$output .= $uploadCount . ' new images found and uploaded</strong><br><br>';

				if ( $total_errors ) {

					foreach ( $this->error_log as $i => $error ) {
						$output .= $i + 1 . '.&nbsp;&nbsp;&nbsp;&nbsp;' . $error . '<br>';
					}

				} else {

					$output .= 'Feed load was successful!';

				}

				echo $output;

				die;

			}

	}

?>
