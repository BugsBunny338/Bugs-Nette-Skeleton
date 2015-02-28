<?php

/**
 * MyString Class
 *
 * Method truncate() truncates raw HTML string (doesn't leave open tag, etc.)
 */
class MyString {

  public static function truncate_html($s, $l, $e = '&hellip;', $isHTML = true) {
      $s = trim($s);
      $e = (strlen(strip_tags($s)) > $l) ? $e : '';
      $i = 0;
      $tags = array();

      if($isHTML) {
          preg_match_all('/<[^>]+>([^<]*)/', $s, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
          foreach($m as $o) {
              if($o[0][1] - $i >= $l) {
                  break;                  
              }
              $t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);
              if($t[0] != '/') {
                  $tags[] = $t;                   
              }
              elseif(end($tags) == substr($t, 1)) {
                  array_pop($tags);                   
              }
              $i += $o[1][1] - $o[0][1];
          }
      }
      $output = substr($s, 0, $l = min(strlen($s), $l + $i)) . (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '') . $e;
      return $output;
  }

  public static function truncate($string, $length, $e = '&hellip;', $isHTML = TRUE)
  {

    // return substr($string, 0, $length);

    $i = 0;
    $tags = array();

    if ($isHTML)
    {
      preg_match_all('/<[^>]+>([^<]*)/', $string, $match, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

      foreach($match as $object) {
        if($object[0][1] - $i >= $length) break;

        $tag = substr(strtok($object[0][0], " \t\n\r\0\x0B>"), 1);

        if($tag[0] != '/') $tags[] = $tag;
        elseif(end($tags) == substr($tag, 1)) array_pop($tags);

        $i += $object[1][1] - $object[0][1];
      }
    }
    return substr($string, 0, $length = min(strlen($string),  $length + $i)) . (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '') . (strlen($string) > $length ? $e : '');
  }

}
