<?php           

class Helper {
	
    public function shortenTextWord($text, $chars=250, $tail='…') {
        if (intval($chars)==0) $chars=250;
        $text=strip_tags($text,'<a>');
        if (function_exists('mb_substr')) {
            if (mb_strlen($text, CHARSET) > $chars) { 
                $text=preg_replace('/\s+?(\S+)?$/', '', mb_substr($text, 0, $chars + 1, CHARSET)) . $tail;
            }
        } else {
            if (strlen($text) > $chars) { 
                $text = preg_replace('/\s+?(\S+)?$/', '', substr($text, 0, $chars + 1)) . $tail;
            }
        }
        return $text;       
    }
    public function generateKeywords($string,$title){
        $num=KEYWORD_LENGTH;
        $stopwords=STOPWORDS;
        $stopwords=explode(', ', $stopwords);
        $string=$string." ".$title;//weight the scales a bit
        $string = preg_replace('#<\s*script\s*(type="text/javascript"\s*)?>(.+)<\s*/script\s*>#smUi','', $string);
        $string = preg_replace('#<\s*style.*>.+<\s*/style\s*\/?>#smUi','', $string);
        $string = preg_replace('/ss+/i', '', $string);
        //get rid of unneccessary stuff
        $string = strip_tags($string);
        //strip punctuation
        $string = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $string);
        //lower case
        $string = strtolower($string);
        $string = html_entity_decode($string);
        $words=explode(' ', $string);
        $words=array_diff($words, $stopwords);
        //get rid of the unneccessary- no "the" in the keywords.
        $keywordCounts = array_count_values($words);
        //flip
        arsort($keywordCounts, SORT_NUMERIC);
        //rank em
        $keywordList=array_keys($keywordCounts);
        //flip em again
        $keywords = array_slice($keywordList, 0, $num); 
        //returns an array of the $num most popular words.
        $str='';
        foreach($keywords as $word){
            $str.=$word.', ';
        }
        return $str;
    }
}

?>