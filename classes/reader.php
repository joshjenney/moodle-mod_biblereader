<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local library file for the mod_biblereader plugin. These are non-standard
 * functions that are used only by the mod_biblereader plugin.
 *
 * @package   mod_biblereader
 * @copyright 2024, Josh Jenney <josh@n2nministries.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace mod_biblereader;

 /** Make sure this isn't being directly accessed */
 defined('MOODLE_INTERNAL') || die();

 class reader {
   private $version = 'KJV';

   private $translations = array(
     array(
       'version' => 'KJV',
       'description' => 'King James Version',
     ),

     array(
       'version' => 'ASV',
       'description' => 'American Standard Version',
     ),

     array(
       'version' => 'YLT',
       'description' => 'Young’s Literal Translation',
     ),
   );

   private $response = array();

   public function set_version($version = 'KJV') {

      foreach($this->translations as $index => $translation){
        if($version == $translation['version']) {
          $this->translations[$index]['selected'] = 'true';
          $this->version = $version;
        }
      }
   }

   public function get_versions(){
     return $this->translations;
   }

   public function curl_api_bible($passage = '') {
     $apikey = 'c5237918f0d17bf20d067d75cd8dde91';

      /**
       * STYLE FORMATTING OPTIONS
       * available pre-defined styles
       * fullyFormatted
       * oneVersePerLine
       * oneVersePerLineFullReference
       * quotation
       * simpleParagraphs
       * bibleTextOnly
       * orationOneParagraph
       * orationOneVersePerLine
       * orationBibleParagraphs
       * fullyFormattedWithFootnotes
       **/
     $style = 'orationOneVersePerLine';

      /**
       * BIBLE / DESCRIPTION
       *
       * ASV 		American Standard Version
       * ARVANDYKE 	Arabic Bible (Smith & Van Dyke)
       * KJV 		Authorized Version
       * LSG 		La Bible Louis Segond 1910
       * BYZ 		Byzantine/Majority Textform Greek New Testament
       * DARBY 		1890 Darby Bible
       * Elzevir 	Elzevir Textus Receptus (1624) with Morphology
       * ITDIODATI1649 	Giovanni Diodati Bibbia
       * EMPHBBL 	The Emphasized Bible
       * KJV1900 	King James Version
       * KJVAPOC 	The King James Version Apocrypha
       * LEB 		The Lexham English Bible
       * SCRMORPH 	The New Testament in Greek (Scrivener 1881)
       * FI-RAAMATTU 	Raamattu (1933, 1938)
       * RVR60 		Reina Valera Revisada (1960)
       * RVA 		Reina-Valera Actualizada
       * bb-sbb-rusbt 	Russian Synodal Bible Translation
       * eo-zamenbib 	La Sankta Biblio
       * TR1881 	Scrivener’s Textus Receptus (1881)
       * TR1894MR 	Scrivener’s Textus Receptus (1894) with Morphology
       * SVV 		Statenvertaling
       * STEPHENS 	Stephen’s Textus Receptus (1550)
       * TANAKH 	Tanakh, The Holy Scriptures
       * wbtc-ptbrnt 	Versão Fácil De Ler
       * WH1881MR 	Westcott and Hort Greek New Testament (1881) with Morphology
       * YLT 		Young’s Literal Translation
       **/

      if(!in_array($this->version, array('KJV','ASV','YLT')))
        $version = 'KJV';
      else
        $version = $this->version;

      $request = "https://api.biblia.com/v1/bible/content/{$version}.json";

      $defaults = array(
          CURLOPT_URL            => $request,
          CURLOPT_CUSTOMREQUEST  => 'GET',
          CURLOPT_HTTPHEADER     => array('accept: application/json'),
          CURLOPT_POSTFIELDS     => [
              'passage' => $passage ,
              'style'   => $style,
              'key'     => $apikey
          ],
          CURLOPT_RETURNTRANSFER => 'true'
      );

      $ch = curl_init();
      curl_setopt_array($ch, $defaults);
      $content = curl_exec($ch);
      // DEBUG: $content = false;
      $status = curl_getinfo($ch);
      $output = array();
      if( $content === false ){
          // trigger_error(curl_error($ch));
          // trigger_error('Unable to communicate with Bible Reading API. Please submit a help Desk Ticket for assistance.');
          $output[] = array('error' => '<div class="alert alert-warning" role="alert">Please contact Help Desk for assistance.<br>(error: empty response from bible reading api, code: 204)</div>');
      } else {
          // debug: var_dump($content);

          // returns array( [] => array('verse' => '...'))
          $data = json_decode($content, true);
          $verse_collection = explode("\r\n", $data['text']);
          foreach($verse_collection as $index => $verse)
          {
              // skip title
              if($index == 0)
                continue;
              $output[] = array('verse' => $verse);
          }
      }
      curl_close($ch);

      // save response
      $this->response = $output;
   }

   public function curl_example_data() {

     // TODO: $this->translation_version

     /*
     https://labs.bible.org/api/?passage=Genesis 1&formatting=para&type=json&callback=myCallback&?callback=jQuery191046479199809776117_1709837115237&_=1709837115238
     myCallback([{"bookname":"Genesis","chapter":"1","verse":"1","text":"<p class=\"bodytext\">In the beginning God created the heavens and the earth.<\/p>"},{"bookname":"Genesis","chapter":"1","verse":"2","text":"<p class=\"bodytext\">Now the earth was without shape and empty, and darkness was over the surface of the watery deep, but the Spirit of God was moving over the surface of the water. "},{"bookname":"Genesis","chapter":"1","verse":"3","text":"God said, \u201cLet there be light.\u201d And there was light! "},{"bookname":"Genesis","chapter":"1","verse":"4","text":"God saw that the light was good, so God separated the light from the darkness. "},{"bookname":"Genesis","chapter":"1","verse":"5","text":"God called the light \u201cday\u201d and the darkness \u201cnight.\u201d There was evening, and there was morning, marking the first day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"6","text":"<p class=\"bodytext\">God said, \u201cLet there be an expanse in the midst of the waters and let it separate water from water.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"7","text":"So God made the expanse and separated the water under the expanse from the water above it. It was so. "},{"bookname":"Genesis","chapter":"1","verse":"8","text":"God called the expanse \u201csky.\u201d There was evening, and there was morning, a second day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"9","text":"<p class=\"bodytext\">God said, \u201cLet the water under the sky be gathered to one place and let dry ground appear.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"10","text":"God called the dry ground \u201cland\u201d and the gathered waters he called \u201cseas.\u201d God saw that it was good. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"11","text":"<p class=\"bodytext\">God said, \u201cLet the land produce vegetation: plants yielding seeds and trees on the land bearing fruit with seed in it, according to their kinds.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"12","text":"The land produced vegetation\u2014plants yielding seeds according to their kinds, and trees bearing fruit with seed in it according to their kinds. God saw that it was good. "},{"bookname":"Genesis","chapter":"1","verse":"13","text":"There was evening, and there was morning, a third day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"14","text":"<p class=\"bodytext\">God said, \u201cLet there be lights in the expanse of the sky to separate the day from the night, and let them be signs to indicate seasons and days and years, "},{"bookname":"Genesis","chapter":"1","verse":"15","text":"and let them serve as lights in the expanse of the sky to give light on the earth.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"16","text":"God made two great lights\u2014the greater light to rule over the day and the lesser light to rule over the night. He made the stars also."},{"bookname":"Genesis","chapter":"1","verse":"17","text":"God placed the lights in the expanse of the sky to shine on the earth, "},{"bookname":"Genesis","chapter":"1","verse":"18","text":"to preside over the day and the night, and to separate the light from the darkness. God saw that it was good. "},{"bookname":"Genesis","chapter":"1","verse":"19","text":"There was evening, and there was morning, a fourth day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"20","text":"<p class=\"bodytext\">God said, \u201cLet the water swarm with swarms of living creatures and let birds fly above the earth across the expanse of the sky.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"21","text":"God created the great sea creatures and every living and moving thing with which the water swarmed, according to their kinds, and every winged bird according to its kind. God saw that it was good. "},{"bookname":"Genesis","chapter":"1","verse":"22","text":"God blessed them and said, \u201cBe fruitful and multiply and fill the water in the seas, and let the birds multiply on the earth.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"23","text":"There was evening, and there was morning, a fifth day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"24","text":"<p class=\"bodytext\">God said, \u201cLet the land produce living creatures according to their kinds: cattle, creeping things, and wild animals, each according to its kind.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"25","text":"God made the wild animals according to their kinds, the cattle according to their kinds, and all the creatures that creep along the ground according to their kinds. God saw that it was good. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"26","text":"<p class=\"bodytext\">Then God said, \u201cLet us make humankind in our image, after our likeness, so they may rule over the fish of the sea and the birds of the air, over the cattle, and over all the earth, and over all the creatures that move on the earth.\u201d <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"27","text":"<p class=\"poetry\">God created humankind in his own image, <p class=\"poetry\">in the image of God he created them, <p class=\"poetry\">male and female he created them.<\/p>"},{"bookname":"Genesis","chapter":"1","verse":"28","text":"<p class=\"bodytext\">God blessed them and said to them, \u201cBe fruitful and multiply! Fill the earth and subdue it! Rule over the fish of the sea and the birds of the air and every creature that moves on the ground.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"29","text":"Then God said, \u201cI now give you every seed-bearing plant on the face of the entire earth and every tree that has fruit with seed in it. They will be yours for food. "},{"bookname":"Genesis","chapter":"1","verse":"30","text":"And to all the animals of the earth, and to every bird of the air, and to all the creatures that move on the ground\u2014everything that has living breath in it\u2014I give every green plant for food.\u201d It was so.  <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"31","text":"<p class=\"bodytext\">God saw all that he had made\u2014and it was very good! There was evening, and there was morning, the sixth day. <\/p>"}])

     https://labs.bible.org/api/?passage=Genesis 1:1&formatting=para&type=json
     [{"bookname":"Genesis","chapter":"1","verse":"1","text":"<p class=\"bodytext\">In the beginning God created the heavens and the earth.<\/p>"}]

     https://labs.bible.org/api/?passage=Genesis 1&formatting=para&type=json
     [{"bookname":"Genesis","chapter":"1","verse":"1","text":"<p class=\"bodytext\">In the beginning God created the heavens and the earth.<\/p>"},{"bookname":"Genesis","chapter":"1","verse":"2","text":"<p class=\"bodytext\">Now the earth was without shape and empty, and darkness was over the surface of the watery deep, but the Spirit of God was moving over the surface of the water. "},{"bookname":"Genesis","chapter":"1","verse":"3","text":"God said, \u201cLet there be light.\u201d And there was light! "},{"bookname":"Genesis","chapter":"1","verse":"4","text":"God saw that the light was good, so God separated the light from the darkness. "},{"bookname":"Genesis","chapter":"1","verse":"5","text":"God called the light \u201cday\u201d and the darkness \u201cnight.\u201d There was evening, and there was morning, marking the first day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"6","text":"<p class=\"bodytext\">God said, \u201cLet there be an expanse in the midst of the waters and let it separate water from water.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"7","text":"So God made the expanse and separated the water under the expanse from the water above it. It was so. "},{"bookname":"Genesis","chapter":"1","verse":"8","text":"God called the expanse \u201csky.\u201d There was evening, and there was morning, a second day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"9","text":"<p class=\"bodytext\">God said, \u201cLet the water under the sky be gathered to one place and let dry ground appear.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"10","text":"God called the dry ground \u201cland\u201d and the gathered waters he called \u201cseas.\u201d God saw that it was good. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"11","text":"<p class=\"bodytext\">God said, \u201cLet the land produce vegetation: plants yielding seeds and trees on the land bearing fruit with seed in it, according to their kinds.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"12","text":"The land produced vegetation\u2014plants yielding seeds according to their kinds, and trees bearing fruit with seed in it according to their kinds. God saw that it was good. "},{"bookname":"Genesis","chapter":"1","verse":"13","text":"There was evening, and there was morning, a third day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"14","text":"<p class=\"bodytext\">God said, \u201cLet there be lights in the expanse of the sky to separate the day from the night, and let them be signs to indicate seasons and days and years, "},{"bookname":"Genesis","chapter":"1","verse":"15","text":"and let them serve as lights in the expanse of the sky to give light on the earth.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"16","text":"God made two great lights\u2014the greater light to rule over the day and the lesser light to rule over the night. He made the stars also."},{"bookname":"Genesis","chapter":"1","verse":"17","text":"God placed the lights in the expanse of the sky to shine on the earth, "},{"bookname":"Genesis","chapter":"1","verse":"18","text":"to preside over the day and the night, and to separate the light from the darkness. God saw that it was good. "},{"bookname":"Genesis","chapter":"1","verse":"19","text":"There was evening, and there was morning, a fourth day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"20","text":"<p class=\"bodytext\">God said, \u201cLet the water swarm with swarms of living creatures and let birds fly above the earth across the expanse of the sky.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"21","text":"God created the great sea creatures and every living and moving thing with which the water swarmed, according to their kinds, and every winged bird according to its kind. God saw that it was good. "},{"bookname":"Genesis","chapter":"1","verse":"22","text":"God blessed them and said, \u201cBe fruitful and multiply and fill the water in the seas, and let the birds multiply on the earth.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"23","text":"There was evening, and there was morning, a fifth day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"24","text":"<p class=\"bodytext\">God said, \u201cLet the land produce living creatures according to their kinds: cattle, creeping things, and wild animals, each according to its kind.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"25","text":"God made the wild animals according to their kinds, the cattle according to their kinds, and all the creatures that creep along the ground according to their kinds. God saw that it was good. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"26","text":"<p class=\"bodytext\">Then God said, \u201cLet us make humankind in our image, after our likeness, so they may rule over the fish of the sea and the birds of the air, over the cattle, and over all the earth, and over all the creatures that move on the earth.\u201d <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"27","text":"<p class=\"poetry\">God created humankind in his own image, <p class=\"poetry\">in the image of God he created them, <p class=\"poetry\">male and female he created them.<\/p>"},{"bookname":"Genesis","chapter":"1","verse":"28","text":"<p class=\"bodytext\">God blessed them and said to them, \u201cBe fruitful and multiply! Fill the earth and subdue it! Rule over the fish of the sea and the birds of the air and every creature that moves on the ground.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"29","text":"Then God said, \u201cI now give you every seed-bearing plant on the face of the entire earth and every tree that has fruit with seed in it. They will be yours for food. "},{"bookname":"Genesis","chapter":"1","verse":"30","text":"And to all the animals of the earth, and to every bird of the air, and to all the creatures that move on the ground\u2014everything that has living breath in it\u2014I give every green plant for food.\u201d It was so.  <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"31","text":"<p class=\"bodytext\">God saw all that he had made\u2014and it was very good! There was evening, and there was morning, the sixth day. <\/p>"}]
     */

     $json = '[{"bookname":"Genesis","chapter":"1","verse":"1","text":"<p class=\"bodytext\">In the beginning God created the heavens and the earth.<\/p>"},{"bookname":"Genesis","chapter":"1","verse":"2","text":"<p class=\"bodytext\">Now the earth was without shape and empty, and darkness was over the surface of the watery deep, but the Spirit of God was moving over the surface of the water. "},{"bookname":"Genesis","chapter":"1","verse":"3","text":"God said, \u201cLet there be light.\u201d And there was light! "},{"bookname":"Genesis","chapter":"1","verse":"4","text":"God saw that the light was good, so God separated the light from the darkness. "},{"bookname":"Genesis","chapter":"1","verse":"5","text":"God called the light \u201cday\u201d and the darkness \u201cnight.\u201d There was evening, and there was morning, marking the first day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"6","text":"<p class=\"bodytext\">God said, \u201cLet there be an expanse in the midst of the waters and let it separate water from water.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"7","text":"So God made the expanse and separated the water under the expanse from the water above it. It was so. "},{"bookname":"Genesis","chapter":"1","verse":"8","text":"God called the expanse \u201csky.\u201d There was evening, and there was morning, a second day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"9","text":"<p class=\"bodytext\">God said, \u201cLet the water under the sky be gathered to one place and let dry ground appear.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"10","text":"God called the dry ground \u201cland\u201d and the gathered waters he called \u201cseas.\u201d God saw that it was good. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"11","text":"<p class=\"bodytext\">God said, \u201cLet the land produce vegetation: plants yielding seeds and trees on the land bearing fruit with seed in it, according to their kinds.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"12","text":"The land produced vegetation\u2014plants yielding seeds according to their kinds, and trees bearing fruit with seed in it according to their kinds. God saw that it was good. "},{"bookname":"Genesis","chapter":"1","verse":"13","text":"There was evening, and there was morning, a third day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"14","text":"<p class=\"bodytext\">God said, \u201cLet there be lights in the expanse of the sky to separate the day from the night, and let them be signs to indicate seasons and days and years, "},{"bookname":"Genesis","chapter":"1","verse":"15","text":"and let them serve as lights in the expanse of the sky to give light on the earth.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"16","text":"God made two great lights\u2014the greater light to rule over the day and the lesser light to rule over the night. He made the stars also."},{"bookname":"Genesis","chapter":"1","verse":"17","text":"God placed the lights in the expanse of the sky to shine on the earth, "},{"bookname":"Genesis","chapter":"1","verse":"18","text":"to preside over the day and the night, and to separate the light from the darkness. God saw that it was good. "},{"bookname":"Genesis","chapter":"1","verse":"19","text":"There was evening, and there was morning, a fourth day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"20","text":"<p class=\"bodytext\">God said, \u201cLet the water swarm with swarms of living creatures and let birds fly above the earth across the expanse of the sky.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"21","text":"God created the great sea creatures and every living and moving thing with which the water swarmed, according to their kinds, and every winged bird according to its kind. God saw that it was good. "},{"bookname":"Genesis","chapter":"1","verse":"22","text":"God blessed them and said, \u201cBe fruitful and multiply and fill the water in the seas, and let the birds multiply on the earth.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"23","text":"There was evening, and there was morning, a fifth day. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"24","text":"<p class=\"bodytext\">God said, \u201cLet the land produce living creatures according to their kinds: cattle, creeping things, and wild animals, each according to its kind.\u201d It was so. "},{"bookname":"Genesis","chapter":"1","verse":"25","text":"God made the wild animals according to their kinds, the cattle according to their kinds, and all the creatures that creep along the ground according to their kinds. God saw that it was good. <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"26","text":"<p class=\"bodytext\">Then God said, \u201cLet us make humankind in our image, after our likeness, so they may rule over the fish of the sea and the birds of the air, over the cattle, and over all the earth, and over all the creatures that move on the earth.\u201d <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"27","text":"<p class=\"poetry\">God created humankind in his own image, <p class=\"poetry\">in the image of God he created them, <p class=\"poetry\">male and female he created them.<\/p>"},{"bookname":"Genesis","chapter":"1","verse":"28","text":"<p class=\"bodytext\">God blessed them and said to them, \u201cBe fruitful and multiply! Fill the earth and subdue it! Rule over the fish of the sea and the birds of the air and every creature that moves on the ground.\u201d "},{"bookname":"Genesis","chapter":"1","verse":"29","text":"Then God said, \u201cI now give you every seed-bearing plant on the face of the entire earth and every tree that has fruit with seed in it. They will be yours for food. "},{"bookname":"Genesis","chapter":"1","verse":"30","text":"And to all the animals of the earth, and to every bird of the air, and to all the creatures that move on the ground\u2014everything that has living breath in it\u2014I give every green plant for food.\u201d It was so.  <\/p>"},{"bookname":"Genesis","chapter":"1","verse":"31","text":"<p class=\"bodytext\">God saw all that he had made\u2014and it was very good! There was evening, and there was morning, the sixth day. <\/p>"}]';

     $data = array();
     foreach(json_decode($json, true) as $value){
       if(isset($value['verse']) && isset($value['text'])){
        #$data[$value['verse']] = $value['text'];}
        #$data[] = array('verse' => sprintf("%3s", $value['verse']) .' ' .strip_tags($value['text']));
        $data[] = array('verse' => "{$value['verse']} " .strip_tags($value['text']));
        }
      }

     if(false){
       throw new Exception('Unable to fetch passage.');
     }

     return $this->response = $data;
   }


   public function fetch_passage() {
     return $this->response;
   }
 }
