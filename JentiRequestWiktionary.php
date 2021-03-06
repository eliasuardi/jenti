<?php

// Copyright 2006-2015 - NINETY-DEGREES
/*
$url = "http://www.ninety-degrees.net";
$url = "http://it.wiktionary.org/w/api.php?action=query&titles=politica&prop=revisions&rvprop=content&format=xmlfm&continue=";
$context = NULL;
$json = @file_get_contents( $url, FALSE, $context);
echo $json;
exit;
*/

require_once "JentiRequest.php";



///////////////////////////////////////////////////////////////////
// HTTP GET requests to WIKTIONARY
///////////////////////////////////////////////////////////////////
class   JentiRequestWiktionary
extends JentiRequest
{
    public $language_code = "it";
    
    function __construct( $args=null)
    { 
        parent::__construct( $args);

        $this->service_endpoint   = "http://it.wiktionary.org";
        $this->service_name = "Wikizionario";
    }



    //////// get word request
    function get_word( $word)
    {
        $url = $this->service_endpoint . "/wiki/" . urlencode( $word);
        if ($this->get_web_page( $url))
        { 
            if (!$this->wiktionary_error())
            { 
              return( $this->get_word_data( $word));
            }
        }

        return(array());
    }



    //////// parse WIKTIONARY results and return relevant data
    private function get_word_data( $word)
    {
        $word_array = array();

        $sostantivo_definitions = $this->get_word_definitions_from_h3_span(
            $this->xpath->query("/html/body/div/div/div/h3/span[@id='Sostantivo']"));
        if (count($sostantivo_definitions) > 0)
        {
            $word_data["WORD"] = $word;
            $word_data["TYPE"] = "sostantivo";
            $word_data["LANGUAGE_CODE"] = $this->language_code;
            $word_data["DEFINITION_ARRAY"] = $sostantivo_definitions;

            $word_array[] = $word_data;
        }

        $verbo_definitions = $this->get_word_definitions_from_h3_span(
            $this->xpath->query("/html/body/div/div/div/h3/span[@id='Verbo']"));
        if (count($verbo_definitions) > 0)
        { 
            $word_data["WORD"] = $word;
            $word_data["TYPE"] = "verbo";
            $word_data["LANGUAGE_CODE"] = $this->language_code;
            $word_data["DEFINITION_ARRAY"] = $verbo_definitions;

            $word_array[] = $word_data;
        }

        $aggettivo_definitions = $this->get_word_definitions_from_h3_span(
            $this->xpath->query("/html/body/div/div/div/h3/span[@id='Aggettivo']"));
        if (count($aggettivo_definitions) > 0)
        { 
            $word_data["WORD"] = $word;
            $word_data["TYPE"] = "aggettivo";
            $word_data["LANGUAGE_CODE"] = $this->language_code;
            $word_data["DEFINITION_ARRAY"] = $aggettivo_definitions;

            $word_array[] = $word_data;
        }

        if (count($word_array) > 0)
        {
            // add more words to first word
            $word_array[0]["MORE_WORDS"] = $this->get_more_words_from_links(
                $this->xpath->query("/html/body/div/div/div/ul/li/a"));
        }

        if (count($word_array) == 0)
        { 
            $this->error = "JentiRequestWiktionary: Did not find words at url " . $this->url;
        }

        return($word_array);
    }

  
  
    //////// parse html span that contains a word definitions
    // e.g. /html/body/div/div/div/h3/span[@id='Aggettivo']
    private function get_word_definitions_from_h3_span($span)
    {
        $definitions_array = array();

        if ($span->length > 0)
        {        
            $node = $span->item(0)->parentNode;
            while($node && $node->nodeName != "ol" && $node->nodeName != "ul")
            { 
                $node = $node->nextSibling;
            }
            if (!$node)
            {
                return $definitions_array;
            }

            $i = 0;
            $li_definitions = $node->childNodes;
            foreach ($li_definitions as $child_def)
            {
                if ($child_def->nodeName == "li")
                {
                    $definition = "";
                    $tags = "";
                    $definition_tags_array = array();
                    $li_children = $child_def->childNodes;
                    foreach ($li_children as $child)
                    { 
                        if ($child->nodeName == "ul")
                        { 
                            break;
                        }
                        elseif ($child->nodeName == "small")
                        {
                            $tag = utf8_decode($child->textContent);
                            $tags .= $tag;
                            $definition_tags_array[] = trim($tag, "()");
                        }
                        else 
                        {
                            $definition .= " " . utf8_decode($child->textContent);            
                        }
                    }

                    $definition = trim($definition);
                    if (strlen($definition) > 0 
                    &&  substr_count( $definition, 'definizione mancante') == 0)
                    {
                        $definitions_array[$i]["DEFINITION"] = trim(preg_replace('/\s+/', ' ', $definition));
                        $definitions_array[$i]["DEFINITION_SHORT"] = substr(trim(preg_replace('/\s+/', '', $definition)), 0, 10);
                        $definitions_array[$i]["TAGS"] = $tags;
                        $definitions_array[$i]["TAGS_ARRAY"] = $definition_tags_array;
                        $definitions_array[$i]["SOURCE_NAME"] = $this->service_name;
                        $definitions_array[$i]["SOURCE_URL"] = $this->service_endpoint;
                        $i = $i + 1;
                    }
                }
            }
        }
        
        return $definitions_array;
    }
  
    
    
    //////// parse html links that contain word definitions
    // e.g. /html/body/div/div/div/ul/li/a
    private function get_more_words_from_links($links)
    {
        $more_words = array();
        if ($links->length > 0)
        {   
            foreach ($links as $link_node)
            {
                $word = trim($link_node->textContent);
                if (!strpos($word,' '))
                {
                    $more_words[] = utf8_decode($word);
                }
            }
        }
        
        // remove unwanted words
        $more_words = array_diff($more_words, array("Entra", "Registrati"));
        $more_words = array_unique($more_words);
        
        return $more_words;
    }
    
  
    //////// check HTML for errors from WIKTIONARY
    function wiktionary_error()
    {
        $body = $this->xpath->query( '//html/body');
        $text = @strtolower( $body->item(0)->textContent);
        if (substr_count( $text, 'automated requests'))
        { $this->error = "WIKTIONARY ERROR";
          return( TRUE);
        } 
        $this->error = "";
        return( FALSE);
    }

}
