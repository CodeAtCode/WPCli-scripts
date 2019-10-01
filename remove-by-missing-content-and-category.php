<?php

// php remove-by-missing-content-and-category.php > htaccess

// Set variable
$category = 'quiz';
$cat_id = 90;
$search = "[quiz%"; // This case is for a shortcode that is not on those pages

$query = "SELECT p.ID AS postId, p.post_name, (
 SELECT group_concat(t.name SEPARATOR ', ')
  FROM wp_terms t LEFT JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id LEFT JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = '" . $category . "' AND p.ID = tr.object_id
 ) AS category
 FROM wp_posts AS p
 LEFT JOIN wp_term_relationships AS tr ON p.ID = tr.object_id
 LEFT JOIN wp_term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
 WHERE post_type='post' AND post_status='publish' AND tt.term_id = " . $cat_id . " AND p.post_content NOT LIKE '" . $search . "';";
$query = str_replace("\n", ' ', $query);
$query = str_replace("  ", ' ', $query);

exec('wp db query "' . $query . '"  --skip-column-names --xml > ' . $category . '.xml');

$ids = '';
$htaccess = '';
$xmldata = simplexml_load_file( $category . ".xml" ) or die( "Failed to load" );
foreach( $xmldata->children() as $item ) {
 $ids .= $item->field[0] . " ";
 $htaccess .= 'RewriteRule ^/' . trim( $item->field[1] ) . "/$ https://domain.tld [L,R=301]\n";
}

echo $htaccess;

exec('wp post delete ' . $ids . '  --force');
