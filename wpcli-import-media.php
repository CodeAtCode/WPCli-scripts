#! /usr/local/bin/wp eval-file
<?php

$the_query = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'published', 'posts_per_page' => -1 ) );
$imgExts = array( ".gif", ".jpg", ".jpeg", ".png" );
$search = 'http://url-of-the-domain-after-search-replace'; 
$replace = 'http://old-url-to-download';
$wp_upload_dir = wp_upload_dir();

function UR_exists($url){
   $headers=get_headers($url);
   return stripos($headers[0],"200 OK")?true:false;
}

function download_image( $url, $local_path ) {
	try {
		$image = file_get_contents( trim( $url ) );
		file_put_contents( $local_path, $image );
	} catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
		die();
	}
}

if ( $the_query->have_posts() ) {
    while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $content = get_the_content();
        preg_match_all( '@src="([^"]+)"@' , $content, $match );
		$srcs = array_pop( $match );
		if ( !empty( $srcs ) ) {
			echo "Parsing " . get_the_ID() . "\n";
			foreach ( $srcs as &$url ) {
				$pos = strrpos( $url, "." );
				if ( $pos === false ) {
					continue;
				}

				$ext = strtolower( trim( substr( $url, $pos ) ) );
				if ( !in_array( $ext, $imgExts ) ) {
					continue;
				}

				echo ' Found ' . $url . "\n";
				$local_path = str_replace( $search, './wp-content', $url );
				if ( !file_exists( dirname( $local_path ) ) ) {
					echo 'Creating folder ' . dirname( $local_path ) . "\n";
					mkdir( dirname( $local_path ), 0777, true );
				} else {
					echo 'Folder already exists' . "\n";
				}
				$original_url = $url;

				// If image from another domain ignore it
				if ( strpos( $url, $search ) === false ) {
					echo 'Another domain ' . $url . "\n";
					continue;
				}

				if ( !file_exists( $local_path ) ) {
					echo '  Downloading file' . "\n";
					$url = str_replace( $search, $replace, $url );
					download_image( $url, $local_path );
				} else {
					echo 'File already exists' . "\n";
				}

				$pattern = '/\-*(\d+)x(\d+)\.(.*)$/';
				$replacement = '.$3';
				$get_original_image = preg_replace( $pattern, $replacement, $original_url );

				$local_path = str_replace( $search, './wp-content', $get_original_image );
				if ( !file_exists( $local_path ) ) {
					if ( UR_exists( $get_original_image ) ) {
						echo '  Download original file size' . "\n";
						$url = str_replace( $search, $replace, $get_original_image );
						download_image( $get_original_image, $local_path );
					} else {
						$get_original_image = $original_url;
					}
				}

				$attachment_args = array(
					'posts_per_page' => 1,
					'post_type'      => 'attachment',
					'guid'           => $get_original_image
				);
				$attachment_check = new Wp_Query( $attachment_args );

				if ( !$attachment_check->have_posts() ) {
					echo '  Importing file' . "\n";
					$basename = basename( $local_path );
					// Check the type of file. We'll use this as the 'post_mime_type'.
					$filetype = wp_check_filetype( $basename, null );

					// Prepare an array of post data for the attachment.
					$attachment = array(
						'guid'           => $wp_upload_dir['url'] . '/' . str_replace( './', '', $basename ),
						'post_mime_type' => $filetype[ 'type' ],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', $basename ),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);

					// Insert the attachment.
					$attach_id = wp_insert_attachment( $attachment, $local_path );
					update_attached_file( $attach_id, str_replace( './wp-content/uploads/', '', $local_path ) );
				}
			}
		}
    }
}

wp_reset_postdata();
