<?php
/*
 *      sfnt.utils.php - some utilities for the sfnt class
 *
 *      Copyright 2011 Olivier Berten <olivier.berten@gmail.com>
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 3 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

$UnicodeData = array();
function UnicodeData($unidata = "http://www.unicode.org/Public/UNIDATA/UnicodeData.txt") {
	global $UnicodeData;
	if(count($UnicodeData) == 0) {
		if (($handle = fopen($unidata, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
				$UnicodeData[hexdec($data[0])] = array_slice($data, 1);
			}
			fclose($handle);
		}
	}
}

$AdobeGlyphList = array();
function AdobeGlyphList($agl = "https://www.adobe.com/devnet-archive/opentype/archives/aglfn.txt") {
	global $AdobeGlyphList;
	if(count($AdobeGlyphList) == 0) {
		if (($handle = fopen($unidata, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
				if(substr($data[0],0,1) != "#") $AdobeGlyphList[hexdec($data[0])] = array_slice($data, 1);
			}
			fclose($handle);
		}
	}
}

# Unicode 6.3 blocks from http://www.unicode.org/Public/6.3.0/ucd/Blocks.txt
$UnicodeBlocks = array(
	array(0x0000, 0x007F, 'Basic Latin'),
	array(0x0080, 0x00FF, 'Latin-1 Supplement'),
	array(0x0100, 0x017F, 'Latin Extended-A'),
	array(0x0180, 0x024F, 'Latin Extended-B'),
	array(0x0250, 0x02AF, 'IPA Extensions'),
	array(0x02B0, 0x02FF, 'Spacing Modifier Letters'),
	array(0x0300, 0x036F, 'Combining Diacritical Marks'),
	array(0x0370, 0x03FF, 'Greek and Coptic'),
	array(0x0400, 0x04FF, 'Cyrillic'),
	array(0x0500, 0x052F, 'Cyrillic Supplement'),
	array(0x0530, 0x058F, 'Armenian'),
	array(0x0590, 0x05FF, 'Hebrew'),
	array(0x0600, 0x06FF, 'Arabic'),
	array(0x0700, 0x074F, 'Syriac'),
	array(0x0750, 0x077F, 'Arabic Supplement'),
	array(0x0780, 0x07BF, 'Thaana'),
	array(0x07C0, 0x07FF, 'NKo'),
	array(0x0800, 0x083F, 'Samaritan'),
	array(0x0840, 0x085F, 'Mandaic'),
	array(0x08A0, 0x08FF, 'Arabic Extended-A'),
	array(0x0900, 0x097F, 'Devanagari'),
	array(0x0980, 0x09FF, 'Bengali'),
	array(0x0A00, 0x0A7F, 'Gurmukhi'),
	array(0x0A80, 0x0AFF, 'Gujarati'),
	array(0x0B00, 0x0B7F, 'Oriya'),
	array(0x0B80, 0x0BFF, 'Tamil'),
	array(0x0C00, 0x0C7F, 'Telugu'),
	array(0x0C80, 0x0CFF, 'Kannada'),
	array(0x0D00, 0x0D7F, 'Malayalam'),
	array(0x0D80, 0x0DFF, 'Sinhala'),
	array(0x0E00, 0x0E7F, 'Thai'),
	array(0x0E80, 0x0EFF, 'Lao'),
	array(0x0F00, 0x0FFF, 'Tibetan'),
	array(0x1000, 0x109F, 'Myanmar'),
	array(0x10A0, 0x10FF, 'Georgian'),
	array(0x1100, 0x11FF, 'Hangul Jamo'),
	array(0x1200, 0x137F, 'Ethiopic'),
	array(0x1380, 0x139F, 'Ethiopic Supplement'),
	array(0x13A0, 0x13FF, 'Cherokee'),
	array(0x1400, 0x167F, 'Unified Canadian Aboriginal Syllabics'),
	array(0x1680, 0x169F, 'Ogham'),
	array(0x16A0, 0x16FF, 'Runic'),
	array(0x1700, 0x171F, 'Tagalog'),
	array(0x1720, 0x173F, 'Hanunoo'),
	array(0x1740, 0x175F, 'Buhid'),
	array(0x1760, 0x177F, 'Tagbanwa'),
	array(0x1780, 0x17FF, 'Khmer'),
	array(0x1800, 0x18AF, 'Mongolian'),
	array(0x18B0, 0x18FF, 'Unified Canadian Aboriginal Syllabics Extended'),
	array(0x1900, 0x194F, 'Limbu'),
	array(0x1950, 0x197F, 'Tai Le'),
	array(0x1980, 0x19DF, 'New Tai Lue'),
	array(0x19E0, 0x19FF, 'Khmer Symbols'),
	array(0x1A00, 0x1A1F, 'Buginese'),
	array(0x1A20, 0x1AAF, 'Tai Tham'),
	array(0x1B00, 0x1B7F, 'Balinese'),
	array(0x1B80, 0x1BBF, 'Sundanese'),
	array(0x1BC0, 0x1BFF, 'Batak'),
	array(0x1C00, 0x1C4F, 'Lepcha'),
	array(0x1C50, 0x1C7F, 'Ol Chiki'),
	array(0x1CC0, 0x1CCF, 'Sundanese Supplement'),
	array(0x1CD0, 0x1CFF, 'Vedic Extensions'),
	array(0x1D00, 0x1D7F, 'Phonetic Extensions'),
	array(0x1D80, 0x1DBF, 'Phonetic Extensions Supplement'),
	array(0x1DC0, 0x1DFF, 'Combining Diacritical Marks Supplement'),
	array(0x1E00, 0x1EFF, 'Latin Extended Additional'),
	array(0x1F00, 0x1FFF, 'Greek Extended'),
	array(0x2000, 0x206F, 'General Punctuation'),
	array(0x2070, 0x209F, 'Superscripts and Subscripts'),
	array(0x20A0, 0x20CF, 'Currency Symbols'),
	array(0x20D0, 0x20FF, 'Combining Diacritical Marks for Symbols'),
	array(0x2100, 0x214F, 'Letterlike Symbols'),
	array(0x2150, 0x218F, 'Number Forms'),
	array(0x2190, 0x21FF, 'Arrows'),
	array(0x2200, 0x22FF, 'Mathematical Operators'),
	array(0x2300, 0x23FF, 'Miscellaneous Technical'),
	array(0x2400, 0x243F, 'Control Pictures'),
	array(0x2440, 0x245F, 'Optical Character Recognition'),
	array(0x2460, 0x24FF, 'Enclosed Alphanumerics'),
	array(0x2500, 0x257F, 'Box Drawing'),
	array(0x2580, 0x259F, 'Block Elements'),
	array(0x25A0, 0x25FF, 'Geometric Shapes'),
	array(0x2600, 0x26FF, 'Miscellaneous Symbols'),
	array(0x2700, 0x27BF, 'Dingbats'),
	array(0x27C0, 0x27EF, 'Miscellaneous Mathematical Symbols-A'),
	array(0x27F0, 0x27FF, 'Supplemental Arrows-A'),
	array(0x2800, 0x28FF, 'Braille Patterns'),
	array(0x2900, 0x297F, 'Supplemental Arrows-B'),
	array(0x2980, 0x29FF, 'Miscellaneous Mathematical Symbols-B'),
	array(0x2A00, 0x2AFF, 'Supplemental Mathematical Operators'),
	array(0x2B00, 0x2BFF, 'Miscellaneous Symbols and Arrows'),
	array(0x2C00, 0x2C5F, 'Glagolitic'),
	array(0x2C60, 0x2C7F, 'Latin Extended-C'),
	array(0x2C80, 0x2CFF, 'Coptic'),
	array(0x2D00, 0x2D2F, 'Georgian Supplement'),
	array(0x2D30, 0x2D7F, 'Tifinagh'),
	array(0x2D80, 0x2DDF, 'Ethiopic Extended'),
	array(0x2DE0, 0x2DFF, 'Cyrillic Extended-A'),
	array(0x2E00, 0x2E7F, 'Supplemental Punctuation'),
	array(0x2E80, 0x2EFF, 'CJK Radicals Supplement'),
	array(0x2F00, 0x2FDF, 'Kangxi Radicals'),
	array(0x2FF0, 0x2FFF, 'Ideographic Description Characters'),
	array(0x3000, 0x303F, 'CJK Symbols and Punctuation'),
	array(0x3040, 0x309F, 'Hiragana'),
	array(0x30A0, 0x30FF, 'Katakana'),
	array(0x3100, 0x312F, 'Bopomofo'),
	array(0x3130, 0x318F, 'Hangul Compatibility Jamo'),
	array(0x3190, 0x319F, 'Kanbun'),
	array(0x31A0, 0x31BF, 'Bopomofo Extended'),
	array(0x31C0, 0x31EF, 'CJK Strokes'),
	array(0x31F0, 0x31FF, 'Katakana Phonetic Extensions'),
	array(0x3200, 0x32FF, 'Enclosed CJK Letters and Months'),
	array(0x3300, 0x33FF, 'CJK Compatibility'),
	array(0x3400, 0x4DBF, 'CJK Unified Ideographs Extension A'),
	array(0x4DC0, 0x4DFF, 'Yijing Hexagram Symbols'),
	array(0x4E00, 0x9FFF, 'CJK Unified Ideographs'),
	array(0xA000, 0xA48F, 'Yi Syllables'),
	array(0xA490, 0xA4CF, 'Yi Radicals'),
	array(0xA4D0, 0xA4FF, 'Lisu'),
	array(0xA500, 0xA63F, 'Vai'),
	array(0xA640, 0xA69F, 'Cyrillic Extended-B'),
	array(0xA6A0, 0xA6FF, 'Bamum'),
	array(0xA700, 0xA71F, 'Modifier Tone Letters'),
	array(0xA720, 0xA7FF, 'Latin Extended-D'),
	array(0xA800, 0xA82F, 'Syloti Nagri'),
	array(0xA830, 0xA83F, 'Common Indic Number Forms'),
	array(0xA840, 0xA87F, 'Phags-pa'),
	array(0xA880, 0xA8DF, 'Saurashtra'),
	array(0xA8E0, 0xA8FF, 'Devanagari Extended'),
	array(0xA900, 0xA92F, 'Kayah Li'),
	array(0xA930, 0xA95F, 'Rejang'),
	array(0xA960, 0xA97F, 'Hangul Jamo Extended-A'),
	array(0xA980, 0xA9DF, 'Javanese'),
	array(0xAA00, 0xAA5F, 'Cham'),
	array(0xAA60, 0xAA7F, 'Myanmar Extended-A'),
	array(0xAA80, 0xAADF, 'Tai Viet'),
	array(0xAAE0, 0xAAFF, 'Meetei Mayek Extensions'),
	array(0xAB00, 0xAB2F, 'Ethiopic Extended-A'),
	array(0xABC0, 0xABFF, 'Meetei Mayek'),
	array(0xAC00, 0xD7AF, 'Hangul Syllables'),
	array(0xD7B0, 0xD7FF, 'Hangul Jamo Extended-B'),
	array(0xD800, 0xDB7F, 'High Surrogates'),
	array(0xDB80, 0xDBFF, 'High Private Use Surrogates'),
	array(0xDC00, 0xDFFF, 'Low Surrogates'),
	array(0xE000, 0xF8FF, 'Private Use Area'),
	array(0xF900, 0xFAFF, 'CJK Compatibility Ideographs'),
	array(0xFB00, 0xFB4F, 'Alphabetic Presentation Forms'),
	array(0xFB50, 0xFDFF, 'Arabic Presentation Forms-A'),
	array(0xFE00, 0xFE0F, 'Variation Selectors'),
	array(0xFE10, 0xFE1F, 'Vertical Forms'),
	array(0xFE20, 0xFE2F, 'Combining Half Marks'),
	array(0xFE30, 0xFE4F, 'CJK Compatibility Forms'),
	array(0xFE50, 0xFE6F, 'Small Form Variants'),
	array(0xFE70, 0xFEFF, 'Arabic Presentation Forms-B'),
	array(0xFF00, 0xFFEF, 'Halfwidth and Fullwidth Forms'),
	array(0xFFF0, 0xFFFF, 'Specials'),
	array(0x10000, 0x1007F, 'Linear B Syllabary'),
	array(0x10080, 0x100FF, 'Linear B Ideograms'),
	array(0x10100, 0x1013F, 'Aegean Numbers'),
	array(0x10140, 0x1018F, 'Ancient Greek Numbers'),
	array(0x10190, 0x101CF, 'Ancient Symbols'),
	array(0x101D0, 0x101FF, 'Phaistos Disc'),
	array(0x10280, 0x1029F, 'Lycian'),
	array(0x102A0, 0x102DF, 'Carian'),
	array(0x10300, 0x1032F, 'Old Italic'),
	array(0x10330, 0x1034F, 'Gothic'),
	array(0x10380, 0x1039F, 'Ugaritic'),
	array(0x103A0, 0x103DF, 'Old Persian'),
	array(0x10400, 0x1044F, 'Deseret'),
	array(0x10450, 0x1047F, 'Shavian'),
	array(0x10480, 0x104AF, 'Osmanya'),
	array(0x10800, 0x1083F, 'Cypriot Syllabary'),
	array(0x10840, 0x1085F, 'Imperial Aramaic'),
	array(0x10900, 0x1091F, 'Phoenician'),
	array(0x10920, 0x1093F, 'Lydian'),
	array(0x10980, 0x1099F, 'Meroitic Hieroglyphs'),
	array(0x109A0, 0x109FF, 'Meroitic Cursive'),
	array(0x10A00, 0x10A5F, 'Kharoshthi'),
	array(0x10A60, 0x10A7F, 'Old South Arabian'),
	array(0x10B00, 0x10B3F, 'Avestan'),
	array(0x10B40, 0x10B5F, 'Inscriptional Parthian'),
	array(0x10B60, 0x10B7F, 'Inscriptional Pahlavi'),
	array(0x10C00, 0x10C4F, 'Old Turkic'),
	array(0x10E60, 0x10E7F, 'Rumi Numeral Symbols'),
	array(0x11000, 0x1107F, 'Brahmi'),
	array(0x11080, 0x110CF, 'Kaithi'),
	array(0x110D0, 0x110FF, 'Sora Sompeng'),
	array(0x11100, 0x1114F, 'Chakma'),
	array(0x11180, 0x111DF, 'Sharada'),
	array(0x11680, 0x116CF, 'Takri'),
	array(0x12000, 0x123FF, 'Cuneiform'),
	array(0x12400, 0x1247F, 'Cuneiform Numbers and Punctuation'),
	array(0x13000, 0x1342F, 'Egyptian Hieroglyphs'),
	array(0x16800, 0x16A3F, 'Bamum Supplement'),
	array(0x16F00, 0x16F9F, 'Miao'),
	array(0x1B000, 0x1B0FF, 'Kana Supplement'),
	array(0x1D000, 0x1D0FF, 'Byzantine Musical Symbols'),
	array(0x1D100, 0x1D1FF, 'Musical Symbols'),
	array(0x1D200, 0x1D24F, 'Ancient Greek Musical Notation'),
	array(0x1D300, 0x1D35F, 'Tai Xuan Jing Symbols'),
	array(0x1D360, 0x1D37F, 'Counting Rod Numerals'),
	array(0x1D400, 0x1D7FF, 'Mathematical Alphanumeric Symbols'),
	array(0x1EE00, 0x1EEFF, 'Arabic Mathematical Alphabetic Symbols'),
	array(0x1F000, 0x1F02F, 'Mahjong Tiles'),
	array(0x1F030, 0x1F09F, 'Domino Tiles'),
	array(0x1F0A0, 0x1F0FF, 'Playing Cards'),
	array(0x1F100, 0x1F1FF, 'Enclosed Alphanumeric Supplement'),
	array(0x1F200, 0x1F2FF, 'Enclosed Ideographic Supplement'),
	array(0x1F300, 0x1F5FF, 'Miscellaneous Symbols And Pictographs'),
	array(0x1F600, 0x1F64F, 'Emoticons'),
	array(0x1F680, 0x1F6FF, 'Transport And Map Symbols'),
	array(0x1F700, 0x1F77F, 'Alchemical Symbols'),
	array(0x20000, 0x2A6DF, 'CJK Unified Ideographs Extension B'),
	array(0x2A700, 0x2B73F, 'CJK Unified Ideographs Extension C'),
	array(0x2B740, 0x2B81F, 'CJK Unified Ideographs Extension D'),
	array(0x2F800, 0x2FA1F, 'CJK Compatibility Ideographs Supplement'),
	array(0xE0000, 0xE007F, 'Tags'),
	array(0xE0100, 0xE01EF, 'Variation Selectors Supplement'),
	array(0xF0000, 0xFFFFF, 'Supplementary Private Use Area-A'),
	array(0x100000, 0x10FFFF, 'Supplementary Private Use Area-B'));

# Apple's Font Feature Registry from https://developer.apple.com/fonts/registry/
$AppleFeatures = array(
	0 => "All Typographic Features",
	1 => "Ligatures",
	2 => "Cursive Connection",
	3 => "Letter Case",
	4 => "Vertical Substitution",
	5 => "Linguistic Rearrangement",
	6 => "Number Spacing",
	8 => "Smart Swash",
	9 => "Diacritics",
	10 => "Vertical Position",
	11 => "Fractions",
	13 => "Overlapping Characters",
	14 => "Typographic Extras",
	15 => "Mathematical Extras",
	16 => "Ornament Sets",
	17 => "Character Alternatives",
	18 => "Design Complexity",
	19 => "Style Options",
	20 => "Character Shape",
	21 => "Number Case",
	22 => "Text Spacing",
	23 => "Translitteration",
	24 => "Annotation",
	25 => "Kana Spacing",
	26 => "Ideographic Spacing",
	100 => "Adobe Character Spacing",
	101 => "Adobe Kana Spacing",
	102 => "Adobe Kanji Spacing",
	103 => "CJK Roman Spacing",
	104 => "Adobe Square Ligatures",
);
$AppleFeaturesSettings = array(
	0 => array(
		0 => "All Type Features On",
		1 => "All Type Features Off",
	),
	1 => array(
		0 => "Required Ligatures On",
		1 => "Required Ligatures Off",
		2 => "Common Ligatures On",
		3 => "Common Ligatures Off",
		4 => "Rare Ligatures On",
		5 => "Rare Ligatures Off",
		6 => "Logos On",
		7 => "Logos Off",
		8 => "Rebus Pictures On",
		9 => "Rebus Pictures Off",
		10 => "Diphthong Ligatures On",
		11 => "Diphthong Ligatures Off",
		12 => "Squared Ligatures On",
		13 => "Squared Ligatures Off",
		14 => "Abbrev Squared Ligatures On",
		15 => "Abbrev Squared Ligatures Off",
	),
	2 => array(
		0 => "Unconnected",
		1 => "Partially Connected",
		2 => "Cursive",
	),
	3 => array(
		0 => "Upper And Lower Case",
		1 => "All Caps",
		2 => "All Lower Case",
		3 => "Small Caps",
		4 => "Initial Caps",
		5 => "Initial Caps And Small Caps",
	),
	4 => array(
		0 => "Substitute Vertical Forms On",
		1 => "Substitute Vertical Forms Off",
	),
	5 => array(
		0 => "Linguistic Rearrangement On",
		1 => "Linguistic Rearrangement Off",
	),
	6 => array(
		0 => "Monospaced Numbers",
		1 => "Proportional Numbers",
	),
	8 => array(
		0 => "Word Initial Swashes On",
		1 => "Word Initial Swashes Off",
		2 => "Word Final Swashes On",
		3 => "Word Final Swashes Off",
		4 => "Line Initial Swashes On",
		5 => "Line Initial Swashes Off",
		6 => "Line Final Swashes On",
		7 => "Line Final Swashes Off",
		8 => "Non Final Swashes On",
		9 => "Non Final Swashes Off",
	),
	9 => array(
		0 => "Show Diacritics",
		1 => "Hide Diacritics",
		2 => "Decompose Diacritics",
	),
	10 => array(
		0 => "Normal Position",
		1 => "Superiors",
		2 => "Inferiors Selector",
		3 => "Ordinals",
	),
	11 => array(
		0 => "No Fractions",
		1 => "Vertical Fractions",
		2 => "Diagonal Fractions",
	),
	13 => array(
		0 => "Prevent Overlap On",
		1 => "Prevent Overlap Off",
	),
	14 => array(
		0 => "Hyphens To Em Dash On",
		1 => "Hyphens To Em Dash Off",
		2 => "Hyphen To En Dash On",
		3 => "Hyphen To En Dash Off",
		4 => "Unslashed Zero On",
		5 => "Unslashed Zero Off",
		6 => "Form Interrobang On",
		7 => "Form Interrobang Off",
		8 => "Smart Quotes On",
		9 => "Smart Quotes Off",
		10 => "Periods To Ellipsis On",
		11 => "Periods To Ellipsis Off",
	),
	15 => array(
		0 => "Hyphen To Minus On",
		1 => "Hyphen To Minus Off",
		2 => "Asterisk To Multiply On",
		3 => "Asterisk To Multiply Off",
		4 => "Slash To Divide On",
		5 => "Slash To Divide Off",
		6 => "Inequality Ligatures On",
		7 => "Inequality Ligatures Off",
		8 => "Exponents On",
		9 => "Exponents Off",
	),
	16 => array(
		0 => "No Ornaments",
		1 => "Dingbats",
		2 => "Pi Characters",
		3 => "Fleurons",
		4 => "Decorative Borders",
		5 => "International Symbols",
		6 => "Math Symbols",
	),
	17 => array(
		0 => "No Alternates",
	),
	18 => array(
		0 => "Design Level 1",
		1 => "Design Level 2",
		2 => "Design Level 3",
		3 => "Design Level 4",
		4 => "Design Level 5",
	),
	19 => array(
		0 => "No Style Options",
		1 => "Display Text",
		2 => "Engraved Text",
		3 => "Illuminated Caps",
		4 => "Titling Caps",
		5 => "Tall Caps",
	),
	20 => array(
		0 => "Traditional Characters",
		1 => "Simplified Characters",
		2 => "JIS 1978 Characters",
		3 => "JIS 1983 Characters",
		4 => "JIS 1990 Characters",
		5 => "Traditional Alt One",
		6 => "Traditional Alt Two",
		7 => "Traditional Alt Three",
		8 => "Traditional Alt Four",
		9 => "Traditional Alt Five",
		10 => "Expert Characters",
	),
	21 => array(
		0 => "Lower Case Numbers",
		1 => "Upper Case Numbers",
	),
	22 => array(
		0 => "Proportional Text",
		1 => "Monospaced Text",
		2 => "Half Width Text",
		3 => "Normally Spaced Text",
	),
	23 => array(
		0 => "No Transliteration",
		1 => "Hanja To Hangul",
		2 => "Hiragana To Katakana",
		3 => "Katakana To Hiragana",
		4 => "Kana To Romanization",
		5 => "Romanization To Hiragana",
		6 => "Romanization To Katakana",
		7 => "Hanja To Hangul Alt One",
		8 => "Hanja To Hangul Alt Two",
		9 => "Hanja To Hangul Alt Three",
	),
	24 => array(
		0 => "No Annotation",
		1 => "Box Annotation",
		2 => "Rounded Box Annotation",
		3 => "Circle Annotation",
		4 => "Inverted Circle Annotation",
		5 => "Parenthesis Annotation",
		6 => "Period Annotation",
		7 => "Roman Numeral Annotation",
		8 => "Diamond Annotation Selector",
	),
	25 => array(
		0 => "Full Width Kana",
		1 => "Proportional Kana",
	),
	26 => array(
		0 => "Full Width Ideographs",
		1 => "Proportional Ideographs",
	),
	103 => array(
		0 => "Half Width CJK Roman",
		1 => "Proportional CJK Roman",
		2 => "Default CJK Roman",
		3 => "Full Width CJK Roman",
	),
);
$AppleFeaturesSettings[100] = $AppleFeaturesSettings[22];
$AppleFeaturesSettings[101] = $AppleFeaturesSettings[25];
$AppleFeaturesSettings[102] = $AppleFeaturesSettings[26];
$AppleFeaturesSettings[104] = $AppleFeaturesSettings[1];

# OpenType feature tags from http://www.microsoft.com/typography/otspec/featurelist.htm
$OpenTypeFeatureTags = array(
	'aalt' => 'Access All Alternates',
	'abvf' => 'Above-base Forms',
	'abvm' => 'Above-base Mark Positioning',
	'abvs' => 'Above-base Substitutions',
	'afrc' => 'Alternative Fractions',
	'akhn' => 'Akhands',
	'blwf' => 'Below-base Forms',
	'blwm' => 'Below-base Mark Positioning',
	'blws' => 'Below-base Substitutions',
	'calt' => 'Contextual Alternates',
	'case' => 'Case-Sensitive Forms',
	'ccmp' => 'Glyph Composition / Decomposition',
	'cfar' => 'Conjunct Form After Ro',
	'cjct' => 'Conjunct Forms',
	'clig' => 'Contextual Ligatures',
	'cpct' => 'Centered CJK Punctuation',
	'cpsp' => 'Capital Spacing',
	'cswh' => 'Contextual Swash',
	'curs' => 'Cursive Positioning',
	'c2pc' => 'Petite Capitals From Capitals',
	'c2sc' => 'Small Capitals From Capitals',
	'dist' => 'Distances',
	'dlig' => 'Discretionary Ligatures',
	'dnom' => 'Denominators',
	'expt' => 'Expert Forms',
	'falt' => 'Final Glyph on Line Alternates',
	'fina' => 'Terminal Forms',
	'fin2' => 'Terminal Forms #2',
	'fin3' => 'Terminal Forms #3',
	'frac' => 'Fractions',
	'fwid' => 'Full Widths',
	'half' => 'Half Forms',
	'haln' => 'Halant Forms',
	'halt' => 'Alternate Half Widths',
	'hist' => 'Historical Forms',
	'hkna' => 'Horizontal Kana Alternates',
	'hlig' => 'Historical Ligatures',
	'hngl' => 'Hangul',
	'hojo' => 'Hojo Kanji Forms (JIS X 0212-1990 Kanji Forms)',
	'hwid' => 'Half Widths',
	'init' => 'Initial Forms',
	'isol' => 'Isolated Forms',
	'ital' => 'Italics',
	'jalt' => 'Justification Alternates',
	'jp78' => 'JIS78 Forms',
	'jp83' => 'JIS83 Forms',
	'jp90' => 'JIS90 Forms',
	'jp04' => 'JIS2004 Forms',
	'kern' => 'Kerning',
	'lfbd' => 'Left Bounds',
	'liga' => 'Standard Ligatures',
	'ljmo' => 'Leading Jamo Forms',
	'lnum' => 'Lining Figures',
	'locl' => 'Localized Forms',
	'ltra' => 'Left-to-right alternates',
	'ltrm' => 'Left-to-right mirrored forms',
	'mark' => 'Mark Positioning',
	'medi' => 'Medial Forms',
	'med2' => 'Medial Forms #2',
	'mgrk' => 'Mathematical Greek',
	'mkmk' => 'Mark to Mark Positioning',
	'mset' => 'Mark Positioning via Substitution',
	'nalt' => 'Alternate Annotation Forms',
	'nlck' => 'NLC Kanji Forms',
	'nukt' => 'Nukta Forms',
	'numr' => 'Numerators',
	'onum' => 'Oldstyle Figures',
	'opbd' => 'Optical Bounds',
	'ordn' => 'Ordinals',
	'ornm' => 'Ornaments',
	'palt' => 'Proportional Alternate Widths',
	'pcap' => 'Petite Capitals',
	'pkna' => 'Proportional Kana',
	'pnum' => 'Proportional Figures',
	'pref' => 'Pre-Base Forms',
	'pres' => 'Pre-base Substitutions',
	'pstf' => 'Post-base Forms',
	'psts' => 'Post-base Substitutions',
	'pwid' => 'Proportional Widths',
	'qwid' => 'Quarter Widths',
	'rand' => 'Randomize',
	'rkrf' => 'Rakar Forms',
	'rlig' => 'Required Ligatures',
	'rphf' => 'Reph Forms',
	'rtbd' => 'Right Bounds',
	'rtla' => 'Right-to-left alternates',
	'rtlm' => 'Right-to-left mirrored forms',
	'ruby' => 'Ruby Notation Forms',
	'salt' => 'Stylistic Alternates',
	'sinf' => 'Scientific Inferiors',
	'size' => 'Optical size',
	'smcp' => 'Small Capitals',
	'smpl' => 'Simplified Forms',
	'subs' => 'Subscript',
	'sups' => 'Superscript',
	'swsh' => 'Swash',
	'titl' => 'Titling',
	'tjmo' => 'Trailing Jamo Forms',
	'tnam' => 'Traditional Name Forms',
	'tnum' => 'Tabular Figures',
	'trad' => 'Traditional Forms',
	'twid' => 'Third Widths',
	'unic' => 'Unicase',
	'valt' => 'Alternate Vertical Metrics',
	'vatu' => 'Vattu Variants',
	'vert' => 'Vertical Writing',
	'vhal' => 'Alternate Vertical Half Metrics',
	'vjmo' => 'Vowel Jamo Forms',
	'vkna' => 'Vertical Kana Alternates',
	'vkrn' => 'Vertical Kerning',
	'vpal' => 'Proportional Alternate Vertical Metrics',
	'vrt2' => 'Vertical Alternates and Rotation',
	'zero' => 'Slashed Zero');

for($i = 1; $i <= 99; $i++) {
	$OpenTypeFeatureTags['cv'.str_pad($i, 2, '0', STR_PAD_LEFT)] = 'Character Variant '.$i;
}
for($i = 1; $i <= 20; $i++) {
	$OpenTypeFeatureTags['ss'.str_pad($i, 2, '0', STR_PAD_LEFT)] = 'Stylistic Set '.$i;
}
for($i = 1; $i <= 99; $i++) {
	$OpenTypeFeatureTags['zz'.str_pad($i, 2, '0', STR_PAD_LEFT)] = 'MS VOLT Table '.$i;
}

# LookupType Enumeration table for glyph positioning (http://www.microsoft.com/typography/otspec/GPOS.htm)
$GPOS_LookupTypes = array(
	1 => 'Single adjustment',
	2 => 'Pair adjustment',
	3 => 'Cursive attachment',
	4 => 'MarkToBase attachment',
	5 => 'MarkToLigature attachment',
	6 => 'MarkToMark attachment',
	7 => 'Context positioning',
	8 => 'Chained Context positioning',
	9 => 'Extension positioning');

# LookupType Enumeration table for glyph substitution (http://www.microsoft.com/typography/otspec/GSUB.htm)
$GSUB_LookupTypes = array(1 => 'Single',
	2 => 'Multiple',
	3 => 'Alternate',
	4 => 'Ligature',
	5 => 'Context',
	6 => 'Chaining Context',
	7 => 'Extension Substitution',
	8 => 'Reverse chaining context single');

# GlyphClassDef Enumeration List (http://www.microsoft.com/typography/otspec/GDEF.htm)
$GlyphClassDef = array(1 => 'Base glyph (single character, spacing glyph)',
	2 => 'Ligature glyph (multiple character, spacing glyph)',
	3 => 'Mark glyph (non-spacing combining glyph)',
	4 => 'Component glyph (part of single character, spacing glyph)');

# OpenType script tags from http://www.microsoft.com/typography/otspec/scripttags.htm
$OpenTypeScriptTags = array('arab' => 'Arabic',
	'armn' => 'Armenian',
	'avst' => 'Avestan',
	'bali' => 'Balinese',
	'bamu' => 'Bamum',
	'batk' => 'Batak',
	'beng' => 'Bengali',
	'bng2' => 'Bengali v.2',
	'bopo' => 'Bopomofo',
	'brai' => 'Braille',
	'brah' => 'Brahmi',
	'bugi' => 'Buginese',
	'buhd' => 'Buhid',
	'byzm' => 'Byzantine Music',
	'cans' => 'Canadian Syllabics',
	'cari' => 'Carian',
	'cakm' => 'Chakma',
	'cham' => 'Cham',
	'cher' => 'Cherokee',
	'hani' => 'CJK Ideographic',
	'copt' => 'Coptic',
	'cprt' => 'Cypriot Syllabary',
	'cyrl' => 'Cyrillic',
	'DFLT' => 'Default',
	'dsrt' => 'Deseret',
	'deva' => 'Devanagari',
	'dev2' => 'Devanagari v.2',
	'egyp' => 'Egyptian heiroglyphs',
	'ethi' => 'Ethiopic',
	'geor' => 'Georgian',
	'glag' => 'Glagolitic',
	'goth' => 'Gothic',
	'grek' => 'Greek',
	'gujr' => 'Gujarati',
	'gjr2' => 'Gujarati v.2',
	'guru' => 'Gurmukhi',
	'gur2' => 'Gurmukhi v.2',
	'hang' => 'Hangul',
	'jamo' => 'Hangul Jamo',
	'hano' => 'Hanunoo',
	'hebr' => 'Hebrew',
	'kana' => 'Hiragana',
	'armi' => 'Imperial Aramaic',
	'phli' => 'Inscriptional Pahlavi',
	'prti' => 'Inscriptional Parthian',
	'java' => 'Javanese',
	'kthi' => 'Kaithi',
	'knda' => 'Kannada',
	'knd2' => 'Kannada v.2',
	'kana' => 'Katakana',
	'kali' => 'Kayah Li',
	'khar' => 'Kharosthi',
	'khmr' => 'Khmer',
	'lao' => 'Lao',
	'latn' => 'Latin',
	'lepc' => 'Lepcha',
	'limb' => 'Limbu',
	'linb' => 'Linear B',
	'lisu' => 'Lisu (Fraser)',
	'lyci' => 'Lycian',
	'lydi' => 'Lydian',
	'mlym' => 'Malayalam',
	'mlm2' => 'Malayalam v.2',
	'mand' => 'Mandaic, Mandaean',
	'math' => 'Mathematical Alphanumeric Symbols',
	'mtei' => 'Meitei Mayek (Meithei, Meetei)',
	'merc' => 'Meroitic Cursive',
	'mero' => 'Meroitic Hieroglyphs',
	'mong' => 'Mongolian',
	'musc' => 'Musical Symbols',
	'mymr' => 'Myanmar',
	'talu' => 'New Tai Lue',
	'nko' => 'N\'Ko',
	'ogam' => 'Ogham',
	'olck' => 'Ol Chiki',
	'ital' => 'Old Italic',
	'xpeo' => 'Old Persian Cuneiform',
	'sarb' => 'Old South Arabian',
	'orkh' => 'Old Turkic, Orkhon Runic',
	'orya' => 'Odia (formerly Oriya)',
	'ory2' => 'Odia v.2 (formerly Oriya v.2)',
	'osma' => 'Osmanya',
	'phag' => 'Phags-pa',
	'phnx' => 'Phoenician',
	'rjng' => 'Rejang',
	'runr' => 'Runic',
	'samr' => 'Samaritan',
	'saur' => 'Saurashtra',
	'shrd' => 'Sharada',
	'shaw' => 'Shavian',
	'sinh' => 'Sinhala',
	'sora' => 'Sora Sompeng',
	'xsux' => 'Sumero-Akkadian Cuneiform',
	'sund' => 'Sundanese',
	'sylo' => 'Syloti Nagri',
	'syrc' => 'Syriac',
	'tglg' => 'Tagalog',
	'tagb' => 'Tagbanwa',
	'tale' => 'Tai Le',
	'lana' => 'Tai Tham (Lanna)',
	'tavt' => 'Tai Viet',
	'takr' => 'Takri',
	'taml' => 'Tamil',
	'tml2' => 'Tamil v.2',
	'telu' => 'Telugu',
	'tel2' => 'Telugu v.2',
	'thaa' => 'Thaana',
	'thai' => 'Thai',
	'tibt' => 'Tibetan',
	'tfng' => 'Tifinagh',
	'ugar' => 'Ugaritic Cuneiform',
	'vai' => 'Vai',
	'yi' => 'Yi');

# OpenType language tags from http://www.microsoft.com/typography/otspec/languagetags.htm
$OpenTypeLanguageTags = array('dflt' => 'Default',
	'ABA' => 'Abaza',
	'ABK' => 'Abkhazian',
	'ADY' => 'Adyghe',
	'AFK' => 'Afrikaans',
	'AFR' => 'Afar',
	'AGW' => 'Agaw',
	'ALS' => 'Alsatian',
	'ALT' => 'Altai',
	'AMH' => 'Amharic',
	'APPH' => 'Phonetic transcription—Americanist conventions',
	'ARA' => 'Arabic',
	'ARI' => 'Aari',
	'ARK' => 'Arakanese',
	'ASM' => 'Assamese',
	'ATH' => 'Athapaskan',
	'AVR' => 'Avar',
	'AWA' => 'Awadhi',
	'AYM' => 'Aymara',
	'AZE' => 'Azeri',
	'BAD' => 'Badaga',
	'BAG' => 'Baghelkhandi',
	'BAL' => 'Balkar',
	'BAU' => 'Baule',
	'BBR' => 'Berber',
	'BCH' => 'Bench',
	'BCR' => 'Bible Cree',
	'BEL' => 'Belarussian',
	'BEM' => 'Bemba',
	'BEN' => 'Bengali',
	'BGR' => 'Bulgarian',
	'BHI' => 'Bhili',
	'BHO' => 'Bhojpuri',
	'BIK' => 'Bikol',
	'BIL' => 'Bilen',
	'BKF' => 'Blackfoot',
	'BLI' => 'Balochi',
	'BLN' => 'Balante',
	'BLT' => 'Balti',
	'BMB' => 'Bambara',
	'BML' => 'Bamileke',
	'BOS' => 'Bosnian',
	'BRE' => 'Breton',
	'BRH' => 'Brahui',
	'BRI' => 'Braj Bhasha',
	'BRM' => 'Burmese',
	'BSH' => 'Bashkir',
	'BTI' => 'Beti',
	'CAT' => 'Catalan',
	'CEB' => 'Cebuano',
	'CHE' => 'Chechen',
	'CHG' => 'Chaha Gurage',
	'CHH' => 'Chattisgarhi',
	'CHI' => 'Chichewa',
	'CHK' => 'Chukchi',
	'CHP' => 'Chipewyan',
	'CHR' => 'Cherokee',
	'CHU' => 'Chuvash',
	'CMR' => 'Comorian',
	'COP' => 'Coptic',
	'COS' => 'Corsican',
	'CRE' => 'Cree',
	'CRR' => 'Carrier',
	'CRT' => 'Crimean Tatar',
	'CSL' => 'Church Slavonic',
	'CSY' => 'Czech',
	'DAN' => 'Danish',
	'DAR' => 'Dargwa',
	'DCR' => 'Woods Cree',
	'DEU' => 'German',
	'DGR' => 'Dogri',
	'DHV (deprecated)' => 'Dhivehi',
	'DIV' => 'Dhivehi',
	'DJR' => 'Djerma',
	'DNG' => 'Dangme',
	'DNK' => 'Dinka',
	'DRI' => 'Dari',
	'DUN' => 'Dungan',
	'DZN' => 'Dzongkha',
	'EBI' => 'Ebira',
	'ECR' => 'Eastern Cree',
	'EDO' => 'Edo',
	'EFI' => 'Efik',
	'ELL' => 'Greek',
	'ENG' => 'English',
	'ERZ' => 'Erzya',
	'ESP' => 'Spanish',
	'ETI' => 'Estonian',
	'EUQ' => 'Basque',
	'EVK' => 'Evenki',
	'EVN' => 'Even',
	'EWE' => 'Ewe',
	'FAN' => 'French Antillean',
	'FAR' => 'Farsi',
	'FIN' => 'Finnish',
	'FJI' => 'Fijian',
	'FLE' => 'Flemish',
	'FNE' => 'Forest Nenets',
	'FON' => 'Fon',
	'FOS' => 'Faroese',
	'FRA' => 'French',
	'FRI' => 'Frisian',
	'FRL' => 'Friulian',
	'FTA' => 'Futa',
	'FUL' => 'Fulani',
	'GAD' => 'Ga',
	'GAE' => 'Gaelic',
	'GAG' => 'Gagauz',
	'GAL' => 'Galician',
	'GAR' => 'Garshuni',
	'GAW' => 'Garhwali',
	'GEZ' => 'Ge\'ez',
	'GIL' => 'Gilyak',
	'GMZ' => 'Gumuz',
	'GON' => 'Gondi',
	'GRN' => 'Greenlandic',
	'GRO' => 'Garo',
	'GUA' => 'Guarani',
	'GUJ' => 'Gujarati',
	'HAI' => 'Haitian',
	'HAL' => 'Halam',
	'HAR' => 'Harauti',
	'HAU' => 'Hausa',
	'HAW' => 'Hawaiin',
	'HBN' => 'Hammer-Banna',
	'HIL' => 'Hiligaynon',
	'HIN' => 'Hindi',
	'HMA' => 'High Mari',
	'HND' => 'Hindko',
	'HO' => 'Ho',
	'HRI' => 'Harari',
	'HRV' => 'Croatian',
	'HUN' => 'Hungarian',
	'HYE' => 'Armenian',
	'IBO' => 'Igbo',
	'IJO' => 'Ijo',
	'ILO' => 'Ilokano',
	'IND' => 'Indonesian',
	'ING' => 'Ingush',
	'INU' => 'Inuktitut',
	'IPPH' => 'Phonetic transcription—IPA conventions',
	'IRI' => 'Irish',
	'IRT' => 'Irish Traditional',
	'ISL' => 'Icelandic',
	'ISM' => 'Inari Sami',
	'ITA' => 'Italian',
	'IWR' => 'Hebrew',
	'JAV' => 'Javanese',
	'JII' => 'Yiddish',
	'JAN' => 'Japanese',
	'JUD' => 'Judezmo',
	'JUL' => 'Jula',
	'KAB' => 'Kabardian',
	'KAC' => 'Kachchi',
	'KAL' => 'Kalenjin',
	'KAN' => 'Kannada',
	'KAR' => 'Karachay',
	'KAT' => 'Georgian',
	'KAZ' => 'Kazakh',
	'KEB' => 'Kebena',
	'KGE' => 'Khutsuri Georgian',
	'KHA' => 'Khakass',
	'KHK' => 'Khanty-Kazim',
	'KHM' => 'Khmer',
	'KHS' => 'Khanty-Shurishkar',
	'KHV' => 'Khanty-Vakhi',
	'KHW' => 'Khowar',
	'KIK' => 'Kikuyu',
	'KIR' => 'Kirghiz',
	'KIS' => 'Kisii',
	'KKN' => 'Kokni',
	'KLM' => 'Kalmyk',
	'KMB' => 'Kamba',
	'KMN' => 'Kumaoni',
	'KMO' => 'Komo',
	'KMS' => 'Komso',
	'KNR' => 'Kanuri',
	'KOD' => 'Kodagu',
	'KOH' => 'Korean Old Hangul',
	'KOK' => 'Konkani',
	'KON' => 'Kikongo',
	'KOP' => 'Komi-Permyak',
	'KOR' => 'Korean',
	'KOZ' => 'Komi-Zyrian',
	'KPL' => 'Kpelle',
	'KRI' => 'Krio',
	'KRK' => 'Karakalpak',
	'KRL' => 'Karelian',
	'KRM' => 'Karaim',
	'KRN' => 'Karen',
	'KRT' => 'Koorete',
	'KSH' => 'Kashmiri',
	'KSI' => 'Khasi',
	'KSM' => 'Kildin Sami',
	'KUI' => 'Kui',
	'KUL' => 'Kulvi',
	'KUM' => 'Kumyk',
	'KUR' => 'Kurdish',
	'KUU' => 'Kurukh',
	'KUY' => 'Kuy',
	'KYK' => 'Koryak',
	'LAD' => 'Ladin',
	'LAH' => 'Lahuli',
	'LAK' => 'Lak',
	'LAM' => 'Lambani',
	'LAO' => 'Lao',
	'LAT' => 'Latin',
	'LAZ' => 'Laz',
	'LCR' => 'L-Cree',
	'LDK' => 'Ladakhi',
	'LEZ' => 'Lezgi',
	'LIN' => 'Lingala',
	'LMA' => 'Low Mari',
	'LMB' => 'Limbu',
	'LMW' => 'Lomwe',
	'LSB' => 'Lower Sorbian',
	'LSM' => 'Lule Sami',
	'LTH' => 'Lithuanian',
	'LTZ' => 'Luxembourgish',
	'LUB' => 'Luba',
	'LUG' => 'Luganda',
	'LUH' => 'Luhya',
	'LUO' => 'Luo',
	'LVI' => 'Latvian',
	'MAJ' => 'Majang',
	'MAK' => 'Makua',
	'MAL' => 'Malayalam Traditional',
	'MAN' => 'Mansi',
	'MAP' => 'Mapudungun',
	'MAR' => 'Marathi',
	'MAW' => 'Marwari',
	'MBN' => 'Mbundu',
	'MCH' => 'Manchu',
	'MCR' => 'Moose Cree',
	'MDE' => 'Mende',
	'MEN' => 'Me\'en',
	'MIZ' => 'Mizo',
	'MKD' => 'Macedonian',
	'MLE' => 'Male',
	'MLG' => 'Malagasy',
	'MLN' => 'Malinke',
	'MLR' => 'Malayalam Reformed',
	'MLY' => 'Malay',
	'MND' => 'Mandinka',
	'MNG' => 'Mongolian',
	'MNI' => 'Manipuri',
	'MNK' => 'Maninka',
	'MNX' => 'Manx Gaelic',
	'MOH' => 'Mohawk',
	'MOK' => 'Moksha',
	'MOL' => 'Moldavian',
	'MON' => 'Mon',
	'MOR' => 'Moroccan',
	'MRI' => 'Maori',
	'MTH' => 'Maithili',
	'MTS' => 'Maltese',
	'MUN' => 'Mundari',
	'NAG' => 'Naga-Assamese',
	'NAN' => 'Nanai',
	'NAS' => 'Naskapi',
	'NCR' => 'N-Cree',
	'NDB' => 'Ndebele',
	'NDG' => 'Ndonga',
	'NEP' => 'Nepali',
	'NEW' => 'Newari',
	'NGR' => 'Nagari',
	'NHC' => 'Norway House Cree',
	'NIS' => 'Nisi',
	'NIU' => 'Niuean',
	'NKL' => 'Nkole',
	'NKO' => 'N\'Ko',
	'NLD' => 'Dutch',
	'NOG' => 'Nogai',
	'NOR' => 'Norwegian',
	'NSM' => 'Northern Sami',
	'NTA' => 'Northern Tai',
	'NTO' => 'Esperanto',
	'NYN' => 'Nynorsk',
	'OCI' => 'Occitan',
	'OCR' => 'Oji-Cree',
	'OJB' => 'Ojibway',
	'ORI' => 'Odia (formerly Oriya)',
	'ORO' => 'Oromo',
	'OSS' => 'Ossetian',
	'PAA' => 'Palestinian Aramaic',
	'PAL' => 'Pali',
	'PAN' => 'Punjabi',
	'PAP' => 'Palpa',
	'PAS' => 'Pashto',
	'PGR' => 'Polytonic Greek',
	'PIL' => 'Filipino',
	'PLG' => 'Palaung',
	'PLK' => 'Polish',
	'PRO' => 'Provencal',
	'PTG' => 'Portuguese',
	'QIN' => 'Chin',
	'RAJ' => 'Rajasthani',
	'RCR' => 'R-Cree',
	'RBU' => 'Russian Buriat',
	'RIA' => 'Riang',
	'RMS' => 'Rhaeto-Romanic',
	'ROM' => 'Romanian',
	'ROY' => 'Romany',
	'RSY' => 'Rusyn',
	'RUA' => 'Ruanda',
	'RUS' => 'Russian',
	'SAD' => 'Sadri',
	'SAN' => 'Sanskrit',
	'SAT' => 'Santali',
	'SAY' => 'Sayisi',
	'SEK' => 'Sekota',
	'SEL' => 'Selkup',
	'SGO' => 'Sango',
	'SHN' => 'Shan',
	'SIB' => 'Sibe',
	'SID' => 'Sidamo',
	'SIG' => 'Silte Gurage',
	'SKS' => 'Skolt Sami',
	'SKY' => 'Slovak',
	'SLA' => 'Slavey',
	'SLV' => 'Slovenian',
	'SML' => 'Somali',
	'SMO' => 'Samoan',
	'SNA' => 'Sena',
	'SND' => 'Sindhi',
	'SNH' => 'Sinhalese',
	'SNK' => 'Soninke',
	'SOG' => 'Sodo Gurage',
	'SOT' => 'Sotho',
	'SQI' => 'Albanian',
	'SRB' => 'Serbian',
	'SRK' => 'Saraiki',
	'SRR' => 'Serer',
	'SSL' => 'South Slavey',
	'SSM' => 'Southern Sami',
	'SUR' => 'Suri',
	'SVA' => 'Svan',
	'SVE' => 'Swedish',
	'SWA' => 'Swadaya Aramaic',
	'SWK' => 'Swahili',
	'SWZ' => 'Swazi',
	'SXT' => 'Sutu',
	'SYR' => 'Syriac',
	'TAB' => 'Tabasaran',
	'TAJ' => 'Tajiki',
	'TAM' => 'Tamil',
	'TAT' => 'Tatar',
	'TCR' => 'TH-Cree',
	'TEL' => 'Telugu',
	'TGN' => 'Tongan',
	'TGR' => 'Tigre',
	'TGY' => 'Tigrinya',
	'THA' => 'Thai',
	'THT' => 'Tahitian',
	'TIB' => 'Tibetan',
	'TKM' => 'Turkmen',
	'TMN' => 'Temne',
	'TNA' => 'Tswana',
	'TNE' => 'Tundra Nenets',
	'TNG' => 'Tonga',
	'TOD' => 'Todo',
	'TRK' => 'Turkish',
	'TSG' => 'Tsonga',
	'TUA' => 'Turoyo Aramaic',
	'TUL' => 'Tulu',
	'TUV' => 'Tuvin',
	'TWI' => 'Twi',
	'UDM' => 'Udmurt',
	'UKR' => 'Ukrainian',
	'URD' => 'Urdu',
	'USB' => 'Upper Sorbian',
	'UYG' => 'Uyghur',
	'UZB' => 'Uzbek',
	'VEN' => 'Venda',
	'VIT' => 'Vietnamese',
	'WA' => 'Wa',
	'WAG' => 'Wagdi',
	'WCR' => 'West-Cree',
	'WEL' => 'Welsh',
	'WLF' => 'Wolof',
	'XBD' => 'Tai Lue',
	'XHS' => 'Xhosa',
	'YAK' => 'Sakha',
	'YBA' => 'Yoruba',
	'YCR' => 'Y-Cree',
	'YIC' => 'Yi Classic',
	'YIM' => 'Yi Modern',
	'ZHH' => 'Chinese, Hong Kong SAR',
	'ZHP' => 'Chinese Phonetic',
	'ZHS' => 'Chinese Simplified',
	'ZHT' => 'Chinese Traditional',
	'ZND' => 'Zande',
	'ZUL' => 'Zulu');


$iconv = array(
	0 => array( # Unicode
		0 => 'UTF-16BE', # Unicode 1.0 semantics
		1 => 'UTF-16BE', # Unicode 1.1 semantics
		2 => 'UTF-16BE', # ISO/IEC 10646 semantics
		3 => 'UTF-16BE', # Unicode 2.0 and onwards semantics, Unicode BMP only (cmap subtable formats 0, 4, 6).
		4 => 'UTF-16BE', # Unicode 2.0 and onwards semantics, Unicode full repertoire (cmap subtable formats 0, 4, 6, 10, 12).
		5 => 'UTF-16BE', # Unicode Variation Sequences (cmap subtable format 14).
		6 => 'UTF-16BE'), # Unicode full repertoire (cmap subtable formats 0, 4, 6, 10, 12, 13).
	1 => array( # Macintosh
		0 => 'MAC', # Roman
		1 => 'MAC-JAPANESE', # Japanese
		2 => 'BIG5', # Chinese (Traditional)
		3 => 'EUC-KR', # Korean
		4 => 'ARABIC', # Arabic
		5 => 'HEBREW', # Hebrew
		6 => 'GREEK8', # Greek
		7 => 'MAC-CYRILLIC', # Russian
		8 => 'MAC', # RSymbol
		9 => 'MAC', # Devanagari
		10 => 'MAC', # Gurmukhi
		11 => 'MAC', # Gujarati
		12 => 'MAC', # Oriya
		13 => 'MAC', # Bengali
		14 => 'MAC', # Tamil
		15 => 'MAC', # Telugu
		16 => 'MAC', # Kannada
		17 => 'MAC', # Malayalam
		18 => 'MAC', # Sinhalese
		19 => 'MAC', # Burmese
		20 => 'MAC', # Khmer
		21 => 'MAC', # Thai
		22 => 'MAC', # Laotian
		23 => 'MAC', # Georgian
		24 => 'MAC', # Armenian
		25 => 'GB2312', # Chinese (Simplified)
		26 => 'MAC', # Tibetan
		27 => 'MAC', # Mongolian
		28 => 'MAC', # Geez
		29 => 'MAC-CENTRALEUROPE', # Slavic
		30 => 'MAC', # Vietnamese
		31 => 'MAC', # Sindhi
		32 => 'MAC'), # Uninterpreted
	3 => array( # Windows
		0 => 'UTF-16BE', # Symbol
		1 => 'UTF-16BE', # Unicode BMP (UCS-2)
		2 => 'SHIFT_JIS', # ShiftJIS
		3 => 'GB18030', # PRC
		4 => 'BIG5', # Big5
		5 => 'EUC-KR', # Wansung
		6 => 'JOHAB', # Johab
		10 => 'UTF-32BE')); # Unicode UCS-4

$recode = array(
	0 => array( # Unicode
		0 => 'UTF-16BE', # Unicode 1.0 semantics
		1 => 'UTF-16BE', # Unicode 1.1 semantics
		2 => 'UTF-16BE', # ISO/IEC 10646 semantics
		3 => 'UTF-16BE', # Unicode 2.0 and onwards semantics, Unicode BMP only (cmap subtable formats 0, 4, 6).
		4 => 'UTF-16BE', # Unicode 2.0 and onwards semantics, Unicode full repertoire (cmap subtable formats 0, 4, 6, 10, 12).
		5 => 'UTF-16BE', # Unicode Variation Sequences (cmap subtable format 14).
		6 => 'UTF-16BE'), # Unicode full repertoire (cmap subtable formats 0, 4, 6, 10, 12, 13).
	1 => array( # Macintosh
		0 => 'MacRoman', # Roman
		1 => 'MAC-JAPANESE', # Japanese
		2 => 'BIG5', # Chinese (Traditional)
		3 => 'EUC-KR', # Korean
		4 => 'MacArabic', # Arabic
		5 => 'MacHebrew', # Hebrew
		6 => 'MacGreek', # Greek
		7 => 'MacCyrillic', # Russian
		8 => 'MAC', # RSymbol
		9 => 'MAC', # Devanagari
		10 => 'MAC', # Gurmukhi
		11 => 'MAC', # Gujarati
		12 => 'MAC', # Oriya
		13 => 'MAC', # Bengali
		14 => 'MAC', # Tamil
		15 => 'MAC', # Telugu
		16 => 'MAC', # Kannada
		17 => 'MAC', # Malayalam
		18 => 'MAC', # Sinhalese
		19 => 'MAC', # Burmese
		20 => 'MAC', # Khmer
		21 => 'MAC', # Thai
		22 => 'MAC', # Laotian
		23 => 'MAC', # Georgian
		24 => 'MAC', # Armenian
		25 => 'GB2312', # Chinese (Simplified)
		26 => 'MAC', # Tibetan
		27 => 'MAC', # Mongolian
		28 => 'MAC', # Geez
		29 => 'MacCentralEurope', # Slavic
		30 => 'MAC', # Vietnamese
		31 => 'MAC', # Sindhi
		32 => 'MAC'), # Uninterpreted
	3 => array( # Windows
		0 => 'UTF-16BE', # Symbol
		1 => 'UTF-16BE', # Unicode BMP (UCS-2)
		2 => 'SHIFT_JIS', # ShiftJIS
		3 => 'GB18030', # PRC
		4 => 'BIG5', # Big5
		5 => 'EUC-KR', # Wansung
		6 => 'JOHAB', # Johab
		10 => 'UTF-32BE')); # Unicode UCS-4

function utf8chr($dec) {
	if ($dec < 128) {
		$utf = chr($dec);
	} else if ($dec < 2048) {
		$utf = chr(192 + (($dec - ($dec % 64)) / 64));
		$utf .= chr(128 + ($dec % 64));
	} else {
		$utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
		$utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
		$utf .= chr(128 + ($dec % 64));
	}
	return $utf;
}

function utf8ord($u) {
    $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
    $k1 = ord(substr($k, 0, 1));
    $k2 = ord(substr($k, 1, 1));
    return $k2 * 256 + $k1;
}

function jp($s) {
	return iconv('SHIFT_JIS', 'UTF-8', $s);
}

function my_err_handler($errno, $errstr, $errfile, $errline) {
	if ( preg_match('/iconv/', $errstr) ) {
		throw new Exception('iconv error');
	} else {
		restore_error_handler();
	}
}
set_error_handler("my_err_handler", E_NOTICE);
function decode_nameRecord($nameRecord) {
	global $iconv,$recode;
	if($nameRecord['platformID'] == 1 && $nameRecord['encodingID'] == 1) {
#		print($nameRecord['data']."\n");
		$e = array();
		# iconv doesn't know about mac-japanese. copyright and trademark characters aren't in shift-jis
		$a = explode(chr(0xFD), $nameRecord['data']); # COPYRIGHT SIGN
		foreach($a as $b) {
			$c = explode(chr(0xFE), $b); # TRADE MARK SIGN
			$d = array_map('jp', $c);
			$e[] = implode('™', $d);
		}
		return implode('©', $e);
	} elseif($nameRecord['platformID'] == 0) {
		try {
			return iconv('UTF-16BE','UTF-8',$nameRecord['data']);
		} catch (Exception $e) {
			return $nameRecord['data'];
		}
	} else {
#		return recode($recode[$nameRecord['platformID']][$nameRecord['encodingID']].'..UTF-8',$nameRecord['data']);
		try {
			return iconv($iconv[$nameRecord['platformID']][$nameRecord['encodingID']],'UTF-8',$nameRecord['data']);
		} catch (Exception $e) {
			return $nameRecord['data'];
		}
	}
}

$languages = array( 3 => array(
# Windows Language Code Identifier (LCID)
# http://msdn.microsoft.com/en-us/library/cc233968(v=PROT.10).aspx
# http://msdn.microsoft.com/en-us/library/ff531705(v=office.12).aspx
	1 => "ar",
	2 => "bg",
	3 => "ca",
	4 => "zh-Hans",
	5 => "cs",
	6 => "da",
	7 => "de",
	8 => "el",
	9 => "en",
	10 => "es",
	11 => "fi",
	12 => "fr",
	13 => "he",
	14 => "hu",
	15 => "is",
	16 => "it",
	17 => "ja",
	18 => "ko",
	19 => "nl",
	20 => "no",
	21 => "pl",
	22 => "pt",
	23 => "rm",
	24 => "ro",
	25 => "ru",
	26 => "hr",
	27 => "sk",
	28 => "sq",
	29 => "sv",
	30 => "th",
	31 => "tr",
	32 => "ur",
	33 => "id",
	34 => "uk",
	35 => "be",
	36 => "sl",
	37 => "et",
	38 => "lv",
	39 => "lt",
	40 => "tg",
	41 => "fa",
	42 => "vi",
	43 => "hy",
	44 => "az",
	45 => "eu",
	46 => "hsb",
	47 => "mk",
	50 => "tn",
	52 => "xh",
	53 => "zu",
	54 => "af",
	55 => "ka",
	56 => "fo",
	57 => "hi",
	58 => "mt",
	59 => "se",
	60 => "ga",
	62 => "ms",
	63 => "kk",
	64 => "ky",
	65 => "sw",
	66 => "tk",
	67 => "uz",
	68 => "tt",
	69 => "bn",
	70 => "pa",
	71 => "gu",
	72 => "or",
	73 => "ta",
	74 => "te",
	75 => "kn",
	76 => "ml",
	77 => "as",
	78 => "mr",
	79 => "sa",
	80 => "mn",
	81 => "bo",
	82 => "cy",
	83 => "km",
	84 => "lo",
	86 => "gl",
	87 => "kok",
	90 => "syr",
	91 => "si",
	93 => "iu",
	94 => "am",
	95 => "tzm",
	97 => "ne",
	98 => "fy",
	99 => "ps",
	100 => "fil",
	101 => "dv",
	104 => "ha",
	106 => "yo",
	107 => "quz",
	108 => "nso",
	109 => "ba",
	110 => "lb",
	111 => "kl",
	112 => "ig",
	120 => "ii",
	122 => "arn",
	124 => "moh",
	126 => "br",
	128 => "ug",
	129 => "mi",
	130 => "oc",
	131 => "co",
	132 => "gsw",
	133 => "sah",
	134 => "qut",
	135 => "rw",
	136 => "wo",
	140 => "prs",
	145 => "gd",
	1025 => "ar-SA",
	1026 => "bg-BG",
	1027 => "ca-ES",
	1028 => "zh-TW",
	1029 => "cs-CZ",
	1030 => "da-DK",
	1031 => "de-DE",
	1032 => "el-GR",
	1033 => "en-US",
	1034 => "es-ES_tradnl",
	1035 => "fi-FI",
	1036 => "fr-FR",
	1037 => "he-IL",
	1038 => "hu-HU",
	1039 => "is-IS",
	1040 => "it-IT",
	1041 => "ja-JP",
	1042 => "ko-KR",
	1043 => "nl-NL",
	1044 => "nb-NO",
	1045 => "pl-PL",
	1046 => "pt-BR",
	1047 => "rm-CH",
	1048 => "ro-RO",
	1049 => "ru-RU",
	1050 => "hr-HR",
	1051 => "sk-SK",
	1052 => "sq-AL",
	1053 => "sv-SE",
	1054 => "th-TH",
	1055 => "tr-TR",
	1056 => "ur-PK",
	1057 => "id-ID",
	1058 => "uk-UA",
	1059 => "be-BY",
	1060 => "sl-SI",
	1061 => "et-EE",
	1062 => "lv-LV",
	1063 => "lt-LT",
	1064 => "tg-Cyrl-TJ",
	1065 => "fa-IR",
	1066 => "vi-VN",
	1067 => "hy-AM",
	1068 => "az-Latn-AZ",
	1069 => "eu-ES",
	1070 => "wen-DE",
	1071 => "mk-MK",
	1072 => "st-ZA",
	1073 => "ts-ZA",
	1074 => "tn-ZA",
	1075 => "ven-ZA",
	1076 => "xh-ZA",
	1077 => "zu-ZA",
	1078 => "af-ZA",
	1079 => "ka-GE",
	1080 => "fo-FO",
	1081 => "hi-IN",
	1082 => "mt-MT",
	1083 => "se-NO",
	1084 => "gd-GB",
	1085 => "yi",
	1086 => "ms-MY",
	1087 => "kk-KZ",
	1088 => "ky-KG",
	1089 => "sw-KE",
	1090 => "tk-TM",
	1091 => "uz-Latn-UZ",
	1092 => "tt-RU",
	1093 => "bn-IN",
	1094 => "pa-IN",
	1095 => "gu-IN",
	1096 => "or-IN",
	1097 => "ta-IN",
	1098 => "te-IN",
	1099 => "kn-IN",
	1100 => "ml-IN",
	1101 => "as-IN",
	1102 => "mr-IN",
	1103 => "sa-IN",
	1104 => "mn-MN",
	1105 => "bo-CN",
	1106 => "cy-GB",
	1107 => "km-KH",
	1108 => "lo-LA",
	1109 => "my-MM",
	1110 => "gl-ES",
	1111 => "kok-IN",
	1112 => "mni",
	1113 => "sd-IN",
	1114 => "syr-SY",
	1115 => "si-LK",
	1116 => "chr-US",
	1117 => "iu-Cans-CA ",
	1118 => "am-ET",
	1119 => "tmz",
	1120 => "ks-Arab-IN",
	1121 => "ne-NP",
	1122 => "fy-NL",
	1123 => "ps-AF",
	1124 => "fil-PH",
	1125 => "dv-MV",
	1126 => "bin-NG",
	1127 => "fuv-NG",
	1128 => "ha-Latn-NG",
	1129 => "ibb-NG",
	1130 => "yo-NG",
	1131 => "quz-BO",
	1132 => "nso-ZA",
	1133 => "ba-RU",
	1134 => "lb-LU",
	1135 => "kl-GL",
	1136 => "ig-NG",
	1137 => "kr-NG",
	1138 => "gaz-ET",
	1139 => "ti-ER",
	1140 => "gn-PY",
	1141 => "haw-US",
	1142 => "la",
	1143 => "so-SO",
	1144 => "ii-CN",
	1145 => "pap-AN",
	1146 => "arn-CL",
	1148 => "moh-CA",
	1150 => "br-FR",
	1152 => "ug-Arab-CN",
	1153 => "mi-NZ",
	1154 => "oc-FR",
	1155 => "co-FR",
	1156 => "gsw-FR",
	1157 => "sah-RU",
	1158 => "qut-GT",
	1159 => "rw-RW",
	1160 => "wo-SN",
	1164 => "prs-AF",
	1165 => "plt-MG",
	1169 => "gd-GB",
	2049 => "ar-IQ",
	2052 => "zh-CN",
	2055 => "de-CH",
	2057 => "en-GB",
	2058 => "es-MX",
	2060 => "fr-BE",
	2064 => "it-CH",
	2067 => "nl-BE",
	2068 => "nn-NO",
	2070 => "pt-PT",
	2072 => "ro-MO",
	2073 => "ru-MO",
	2074 => "sr-Latn-CS",
	2077 => "sv-FI",
	2080 => "ur-IN",
	2092 => "az-Cyrl-AZ",
	2094 => "dsb-DE",
	2107 => "se-SE",
	2108 => "ga-IE",
	2110 => "ms-BN",
	2115 => "uz-Cyrl-UZ",
	2117 => "bn-BD",
	2118 => "pa-PK",
	2128 => "mn-Mong-CN",
	2129 => "bo-BT",
	2137 => "sd-PK",
	2141 => "iu-Latn-CA",
	2143 => "tzm-Latn-DZ",
	2144 => "ks-Deva-IN",
	2145 => "ne-IN",
	2155 => "quz-EC",
	2163 => "ti-ET",
	3073 => "ar-EG",
	3076 => "zh-HK",
	3079 => "de-AT",
	3081 => "en-AU",
	3082 => "es-ES",
	3084 => "fr-CA",
	3098 => "sr-Cyrl-CS",
	3131 => "se-FI",
	3167 => "tmz-MA",
	3179 => "quz-PE",
	4097 => "ar-LY",
	4100 => "zh-SG",
	4103 => "de-LU",
	4105 => "en-CA",
	4106 => "es-GT",
	4108 => "fr-CH",
	4122 => "hr-BA",
	4155 => "smj-NO",
	5121 => "ar-DZ",
	5124 => "zh-MO",
	5127 => "de-LI",
	5129 => "en-NZ",
	5130 => "es-CR",
	5132 => "fr-LU",
	5146 => "bs-Latn-BA",
	5179 => "smj-SE",
	6145 => "ar-MA",
	6153 => "en-IE",
	6154 => "es-PA",
	6156 => "fr-MC",
	6170 => "sr-Latn-BA",
	6203 => "sma-NO",
	7169 => "ar-TN",
	7177 => "en-ZA",
	7178 => "es-DO",
	7180 => "fr-029",
	7194 => "sr-Cyrl-BA",
	7227 => "sma-SE",
	8193 => "ar-OM",
	8201 => "en-JM",
	8202 => "es-VE",
	8204 => "fr-RE",
	8218 => "bs-Cyrl-BA",
	8251 => "sms-FI",
	9217 => "ar-YE",
	9225 => "en-029",
	9226 => "es-CO",
	9228 => "fr-CG",
	9242 => "sr-Latn-RS",
	9275 => "smn-FI",
	10241 => "ar-SY",
	10249 => "en-BZ",
	10250 => "es-PE",
	10252 => "fr-SN",
	11265 => "ar-JO",
	10266 => "sr-Cyrl-RS",
	11265 => "ar-JO",
	11273 => "en-TT",
	11274 => "es-AR",
	11276 => "fr-CM",
	11290 => "sr-Latn-ME",
	12289 => "ar-LB",
	12297 => "en-ZW",
	12298 => "es-EC",
	12300 => "fr-CI",
	12314 => "sr-Cyrl-ME",
	13313 => "ar-KW",
	13321 => "en-PH",
	13322 => "es-CL",
	13324 => "fr-ML",
	14337 => "ar-AE",
	14345 => "en-ID",
	14346 => "es-UY",
	14348 => "fr-MA",
	15361 => "ar-BH",
	15369 => "en-HK",
	15370 => "es-PY",
	15372 => "fr-HT",
	16385 => "ar-QA",
	16393 => "en-IN",
	16394 => "es-BO",
	17417 => "en-MY",
	17418 => "es-SV",
	18441 => "en-SG",
	18442 => "es-HN",
	19466 => "es-NI",
	20490 => "es-PR",
	21514 => "es-US",
	25626 => "bs-Cyrl",
	26650 => "bs-Latn",
	27674 => "sr-Cyrl",
	28698 => "sr-Latn",
	28731 => "smn",
	29740 => "az-Cyrl",
	29755 => "sms",
	30724 => "zh",
	30740 => "nn",
	30746 => "bs",
	30764 => "az-Latn",
	30779 => "sma",
	30787 => "uz-Cyrl",
	30800 => "mn-Cyrl",
	30813 => "iu-Cans",
	31748 => "zh-Hant",
	31764 => "nb",
	31770 => "sr",
	31784 => "tg-Cyrl",
	31790 => "dsb",
	31803 => "smj",
	31811 => "uz-Latn",
	31824 => "mn-Mong",
	31837 => "iu-Latn",
	31839 => "tzm-Latn",
	31848 => "ha-Latn",
	58378 => "es-419",
	58380 => "fr-015"),
1 => array(
# Macintosh Language Codes
# from http://www.opensource.apple.com/source/CF/CF-550/CFLocaleIdentifier.c
	0 => "en",
	1 => "fr",
	2 => "de",
	3 => "it",
	4 => "nl",
	5 => "sv",
	6 => "es",
	7 => "da",
	8 => "pt",
	9 => "nb",
	10 => "he",
	11 => "ja",
	12 => "ar",
	13 => "fi",
	14 => "el",
	15 => "is",
	16 => "mt",
	17 => "tr",
	18 => "hr",
	19 => "zh-Hant",
	20 => "ur",
	21 => "hi",
	22 => "th",
	23 => "ko",
	24 => "lt",
	25 => "pl",
	26 => "hu",
	27 => "et",
	28 => "lv",
	29 => "se",
	30 => "fo",
	31 => "fa",
	32 => "ru",
	33 => "zh-Hans",
	34 => "nl-BE",
	35 => "ga",
	36 => "sq",
	37 => "ro",
	38 => "cs",
	39 => "sk",
	40 => "sl",
	41 => "yi",
	42 => "sr",
	43 => "mk",
	44 => "bg",
	45 => "uk",
	46 => "be",
	47 => "uz-Cyrl",
	48 => "kk",
	49 => "az-Cyrl",
	50 => "az-Arab",
	51 => "hy",
	52 => "ka",
	53 => "mo",
	54 => "ky",
	55 => "tg-Cyrl",
	56 => "tk-Cyrl",
	57 => "mn-Mong",
	58 => "mn-Cyrl",
	59 => "ps",
	60 => "ku",
	61 => "ks",
	62 => "sd",
	63 => "bo",
	64 => "ne",
	65 => "sa",
	66 => "mr",
	67 => "bn",
	68 => "as",
	69 => "gu",
	70 => "pa",
	71 => "or",
	72 => "ml",
	73 => "kn",
	74 => "ta",
	75 => "te",
	76 => "si",
	77 => "my",
	78 => "km",
	79 => "lo",
	80 => "vi",
	81 => "id",
	82 => "tl",
	83 => "ms",
	84 => "ms-Arab",
	85 => "am",
	86 => "ti",
	87 => "om",
	88 => "so",
	89 => "sw",
	90 => "rw",
	91 => "rn",
	92 => "ny",
	93 => "mg",
	94 => "eo",
	128 => "cy",
	129 => "eu",
	130 => "ca",
	131 => "la",
	132 => "qu",
	133 => "gn",
	134 => "ay",
	135 => "tt-Cyrl",
	136 => "ug",
	137 => "dz",
	138 => "jv",
	139 => "su",
	140 => "gl",
	141 => "af",
	142 => "br",
	143 => "iu",
	144 => "gd",
	145 => "gv",
	146 => "ga-Latg",
	147 => "to",
	148 => "grc",
	149 => "kl",
	150 => "az-Latn",
	151 => "nn"));

function table_name_simplify($name_table) {
	global $languages;
	foreach ($name_table['names'] as $elem) {
		if($elem['data'] > '') {
			if(in_array($elem['platformID'], array(1,3))) {
				$names_by_id[$elem['nameID']][$elem['platformID'].'..'.$languages[$elem['platformID']][$elem['languageID']]] = htmlspecialchars(decode_nameRecord($elem));
			} else {
				$names_by_id[$elem['nameID']][$elem['platformID']] = htmlspecialchars(decode_nameRecord($elem));
			}
		}
	}
	foreach($names_by_id as $k => $v) {
		$array = array_unique($v);
		if(count($array) == 1) {
			$names_simplified[$k] = array_pop($array);
		} elseif(count($array) == count($v)) {
			foreach($v as $k2 => $v2) {
				$names_simplified[$k][substr($k2,3)] = $v2;
			}
		} else {
			while($cur_name = current($v)) {
				$cur_lang = key($v);
				$lang_tmp = array($cur_lang => $cur_name);
				foreach(array_keys($v) as $l) {
					$c1 = explode('-', substr($cur_lang, 3));
					$l1 = explode('-', substr($l, 3));
					if($l1[0] == $c1[0]) {
						$lang_tmp[$l] = $v[$l];
					}
				}
				$array2 = array_unique($lang_tmp);
				if(count($array2) == 1) {
					$names_simplified[$k][$c1[0]] = $cur_name;
				} else {
					foreach($lang_tmp as $k2 => $v2) {
						$names_simplified[$k][substr($k2,3)] = $v2;
					}
				}
				foreach($lang_tmp as $k2 => $v2) {
					unset($v[$k2]);
				}
			}
		}
	}
	return $names_simplified;
}

function mif($font, $ttc_font = 0) {
	$featureTypes = array(
		0 => 'Rearrangement',
		1 => 'Contextual',
		2 => 'LigatureList',
		4 => 'Noncontextual',
		5 => 'Insertion',
	);
	$name = table_name_simplify($font->table('name', $ttc_font));
	$feat = $font->table('feat', $ttc_font);
	$feat = $feat['featureNames'];
	$morph = $font->table('morx', $ttc_font);
	if(!$morph) $morph = $font->table('mort', $ttc_font);
	$mif = '';
	foreach($morph as $chain) {
		for($e=0;$e < count($chain['featureEntries']);$e++) {
			$feature = $chain['featureEntries'][$e];
			$mif .= 'Type          '."\n";
			$mif .= 'Name          '.(isset($feat[$feature['featureType']]) && isset($name[$feat[$feature['featureType']]['nameIndex']]) && trim(print_one($name[$feat[$feature['featureType']]['nameIndex']])) != "NILL" ? print_one($name[$feat[$feature['featureType']]['nameIndex']]):"NULL")."\n";
			$mif .= 'Namecode      '.$feature['featureType']."\n";
			$mif .= 'Setting       '.(isset($feat[$feature['featureType']]['settings'][$feature['featureSetting']]) && isset($name[$feat[$feature['featureType']]['settings'][$feature['featureSetting']]]) && trim(print_one($name[$feat[$feature['featureType']]['settings'][$feature['featureSetting']]])) != "NILL" ? print_one($name[$feat[$feature['featureType']]['settings'][$feature['featureSetting']]]):"NULL")."\n";
			$mif .= 'Settingcode   '.$feature['featureSetting']."\n";
			$mif .= 'Default       '."\n";
			$mif .= 'Orientation   '."\n";
			$mif .= 'Forward       '."\n";
			$mif .= 'Exclusive     '."\n";
			$mif .= "\n\n";
		}
		/*
		for($s=0;$s < count($chain['subtables']);$s++) {
			$subtable = $chain['subtables'][$s];
			if($subtable['orientationIndependent']) {
				$orientation = 'HV';
			} elseif($subtable['verticalOnly']) {
				$orientation = 'V';
			} else {
				$orientation = 'H';
			}
			$mif .= 'Type          '.$featureTypes[$subtable['subtableType']]."\n";
			$mif .= 'Name          '."\n";
			$mif .= 'Namecode      '."\n";
			$mif .= 'Setting       '."\n";
			$mif .= 'Settingcode   '."\n";
			$mif .= 'Default       '."\n";
			$mif .= 'Orientation   '.$orientation."\n";
			$mif .= 'Forward       '.($subtable['descendingOrder']?'no':'yes')."\n";
			$mif .= 'Exclusive     '."\n";
			$mif .= "\n\n";
		}
		*/
	}
	return $mif;
}

function print_content($content) {
	if(is_array($content)) {
		if(count($content) == 2 && array_key_exists('en', $content) && array_key_exists('en-US',$content)) {
			$array[] = '<span class="langtag">&lt;mac&gt;</span> '.$content['en'];
			$array[] = '<span class="langtag">&lt;win&gt;</span> '.$content['en-US'];
		} else {
			foreach($content as $lang => $value) {
				$array[] = '<span class="langtag">&lt;'.$lang.'&gt;</span> '.$value;
			}
		}
		return implode("<br />\n",$array);
	} else {
			return $content;
	}
}

function print_one($content) {
	if(is_array($content)) {
		if(array_key_exists(0, $content)) {
			return $content[0];
		} elseif(array_key_exists('en-US', $content)) {
			return $content['en-US'];
		} else {
			return $content['en'];
		}
	} else {
			return $content;
	}
}

function chars2glyphs($font, $ttc_font=0) {
	$cmaps = $font->table('cmap', $ttc_font);

	foreach($cmaps as $map) {
		if ($map['format'] == 4) {
			$cmap4 = $map;
			break;
		}
	}
	
	if($cmap4) {
		$cmap = $cmap4;
	} else {
		foreach($cmaps as $map) {
			if ($map['format'] == 6) {
				$cmap = $map;
				break;
			}
		}
		if(!isset($cmap)) $cmap = array('cmap' => array());
	}
	return $cmap['cmap'];
}

?>
