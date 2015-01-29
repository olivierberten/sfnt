<?php
/*
 *      sfnt.class.php - a php class for parsing sfnt and opentype fonts
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

# Based on the OpenType file format specification http://www.microsoft.com/typography/otspec/otff.htm
class sfnt {
	function __construct($fontfile) {
		// Opening the file
		if (file_exists($fontfile)) {
			$this->fh = fopen($fontfile, 'rb');
		} else {
			die("Cannot open file $fontfile");
		}

		$this->TableDirectory = array();
		$this->Tables = array();

		// Reading the Offset Table
		fseek($this->fh, 0);
		$sfntVersion = $this->read_Fixed();
		if(in_array($sfntVersion, array('1.0','2.0','true','OTTO','typ1',chr(0xA5).'kbd',chr(0xA5).'lst'))) {
			$numTables = $this->read_USHORT();
			$searchRange = $this->read_USHORT();
			$entrySelector = $this->read_USHORT();
			$rangeShift = $this->read_USHORT();

			// Reading the Table Directory
			$this->TableDirectory[0] = array();
			$this->Tables[0] = array();
			for($i = 0; $i < $numTables; $i++) {
				$this->TableDirectory[0][$this->read_Tag()] = array('checkSum' => $this->read_ULONG(), 'offset' => $this->read_ULONG(), 'length' => $this->read_ULONG());
			}
			$this->sfntVersion = array($sfntVersion,);
		} elseif($sfntVersion == 'ttcf') {
			$ttcVersion = $this->read_Fixed();
			$numFonts = $this->read_ULONG();
			for($i = 0; $i < $numFonts; $i++) {
				$OffsetTable[$i] = $this->read_ULONG();
				$this->TableDirectory[$i] = array();
				$this->Tables[$i] = array();
			}
			if($ttcVersion == '2.0') {
				$ulDsigTag = $this->read_ULONG();
				$ulDsigLength = $this->read_ULONG();
				$ulDsigOffset = $this->read_ULONG();
			}
			$this->TableDirectory = array();
			$i = 0;
			foreach($OffsetTable as $o) {
				fseek($this->fh, $o);
				$sfntVersion = $this->read_Fixed();
				$numTables = $this->read_USHORT();
				$searchRange = $this->read_USHORT();
				$entrySelector = $this->read_USHORT();
				$rangeShift = $this->read_USHORT();

				// Reading the Table Directory
				for($j = 0; $j < $numTables; $j++) {
					$this->TableDirectory[$i][$this->read_Tag()] = array('checkSum' => $this->read_ULONG(), 'offset' => $this->read_ULONG(), 'length' => $this->read_ULONG());
				}
				$this->sfntVersion[$i] = $sfntVersion;
				$i++;
			}
		} else {
			die("The file $fontfile does not seem to be a sfnt font.");
		}
#		print_r($this->TableDirectory);
	}

	function __destruct() {
		if(isset($this->fh)) {
			fclose($this->fh);
		}
	}
	
	function unichr($dec) {
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

	function read_BYTE($nb = false) {
		if(is_int($nb)) {
			$list = array();
			for($i = 0; $i < $nb; $i++) {
				$list[] = $this->read_BYTE();
			}
			return $list;
		} else {
			$data = unpack('C', fread($this->fh, 1));
			return $data[1];
		}
	}

	function read_CHAR($nb = false) {
		if(is_int($nb)) {
			$list = array();
			for($i = 0; $i < $nb; $i++) {
				$list[] = $this->read_CHAR();
			}
			return $list;
		} else {
			$data = unpack('c', fread($this->fh, 1));
			return $data[1];
		}
	}

	function read_USHORT($nb = false) {
		if(is_int($nb)) {
			$list = array();
			for($i = 0; $i < $nb; $i++) {
				$list[] = $this->read_USHORT();
			}
			return $list;
		} else {
			$data = unpack('n', fread($this->fh, 2));
			return $data[1];
		}
	}

	function read_SHORT($nb = false) {
		if(is_int($nb)) {
			$list = array();
			for($i = 0; $i < $nb; $i++) {
				$list[] = $this->read_SHORT();
			}
			return $list;
		} else {
			$data = unpack('n', fread($this->fh, 2));
			# There's no big-endian signed short in php's unpack function
			if($data[1] >= pow(2, 15)) {
				return $data[1] - pow(2, 16);
			} else {
				return $data[1];
			}
		}
	}

	function read_UINT24() {
#		return hexdec($this->read_HEX(3));
		return $this->read_HEX(3);
	}

	function read_ULONG($nb = false) {
		if(is_int($nb)) {
			$list = array();
			for($i = 0; $i < $nb; $i++) {
				$list[] = $this->read_ULONG();
			}
			return $list;
		} else {
			$data = unpack('N', fread($this->fh, 4));
			return $data[1];
		}
	}

	function read_LONG() {
		$data = unpack('N', fread($this->fh, 4));
		# There's no big-endian signed long in php's unpack function
		if($data[1] > 0x7FFFFFFF) {
			return $data[1] - 0xFFFFFFFF;
		} else {
			return $data[1];
		}
	}

	function read_LONGDATETIME() {
		# There's no 64-bit integer in php's unpack function
#		$data = unpack('H16', fread($this->fh, 8));
#		return hexdec($data[1]);
		$data = unpack('H8/H8', fread($this->fh, 8)); # No font was created after 2038 ;-)
		if(hexdec($data[1]) >= 2082840000) {
			# OpenType/TrueType/MacOS tells dates from January 1, 1904 while POSIX/php tells from January 1, 1970
			return hexdec($data[1])-2082844800;
		} else {
			# but some fonts have the date encoded in POSIX time though and I guess no truetype font was ever created before 1970...
			return hexdec($data[1]);
		}
	}

	function read_Fixed() {
		$data = fread($this->fh, 4);
		$f = unpack('nMajor/H4Minor', $data);
		$Major = $f['Major'];
		$Minor = hexdec($f['Minor'][0]);
		if(in_array($Major, array(29810, 20308, 29817, 29812, 42347, 42348))) {
			return $data;
		} else {
			if($Major > 0x7FFF) { # for $maxp['italicAngle']
				return ($Major - 0xFFFF).'.'.$Minor;
			} else {
				return $Major.'.'.$Minor;
			}
		}
	}

	function read_Tag() {
		return fread($this->fh, 4);
	}

	function read_GlyphID($nb = false) {
		return $this->read_USHORT($nb);
	}

	function read_Offset($nb = false) {
		return $this->read_USHORT($nb);
	}

	function read_uint16($nb = false) {
		return $this->read_USHORT($nb);
	}

	function read_int16($nb = false) {
		return $this->read_SHORT($nb);
	}

	function read_FWord($nb = false) {
		return $this->read_SHORT($nb);
	}

	function read_PascalString($nb = false) {
		if(is_int($nb)) {
			$list = array();
			for($i = 0; $i < $nb; $i++) {
				$list[] = $this->read_PascalString();
			}
			return $list;
		} else {
			$length = $this->read_BYTE();
			if ($length > 0) {
				return fread($this->fh, $length);
			} else {
				return '';
			}
		}
	}

	function read_HEX($bytes = 1) {
		$data = unpack('H'.($bytes*2), fread($this->fh, $bytes));
		return $data[1];
	}

	function read_Flag($bytes = 1) {
		#return hexdec($this->read_HEX($bytes));
		return str_split(strrev(str_pad(decbin(hexdec($this->read_HEX($bytes))),$bytes*8,'0',STR_PAD_LEFT)));
	}

	function read_StateHeader() {
		$stHeader['stateSize'] = $this->read_USHORT();
		$stHeader['classTable'] = $this->read_USHORT();
		$stHeader['stateArray'] = $this->read_USHORT();
		$stHeader['entryTable'] = $this->read_USHORT();
		return($stHeader);
	}

	function read_StateHeaderExtended() {
		$stHeaderX['nClasses'] = $this->read_ULONG();
		$stHeaderX['classTableOffset'] = $this->read_ULONG();
		$stHeaderX['stateArrayOffset'] = $this->read_ULONG();
		$stHeaderX['entryTableOffset'] = $this->read_ULONG();
		return($stHeaderX);
	}

	function read_StateTableX($stHeaderX) {
	}

	function read_AATLookupTable() {
		$lookupTable['format'] = $format = $this->read_USHORT();
		switch($format) {
			case 0: # Simple array format
				$fsHeader = "TODO (Simple array format)";
				break;
			case 2: # Segment single format
				$fsHeader = "TODO (Segment single format)";
				break;
			case 4: # Segment array format
				$fsHeader = "TODO (Segment array format)";
				break;
			case 6: # Single table format
				$fsHeader = "TODO (Simple array format)";
				break;
			case 8: # Trimmed array format
				$fsHeader['firstGlyph'] = $this->read_USHORT();
				$fsHeader['glyphCount'] = $this->read_USHORT();
				for($i = 0; $i < $fsHeader['glyphCount']; $i++) {
					$fsHeader['valueArray'][] = $this->read_USHORT();
				}
		}
		$lookupTable['fsHeader'] = $fsHeader;
		return $lookupTable;
	}

	function list_tables($ttc_font = 0) {
		return array_keys($this->TableDirectory[$ttc_font]);
	}

	function table($tag, $ttc_font = 0) {
		if(!isset($this->TableDirectory[$ttc_font][$tag]) || $this->TableDirectory[$ttc_font][$tag]['length'] == 0) return false;
		if(!array_key_exists($tag, $this->Tables[$ttc_font])) {
			if(trim($tag) == 'name') {
				$this->Tables[$ttc_font][$tag] = $this->table_name($ttc_font);
			} elseif(trim($tag) == 'cmap') {
				$this->Tables[$ttc_font][$tag] = $this->table_cmap($ttc_font);
			} elseif(trim($tag) == 'GSUB') {
				$this->Tables[$ttc_font][$tag] = $this->table_gposgsub($tag, $ttc_font);
			} elseif(trim($tag) == 'GPOS') {
				$this->Tables[$ttc_font][$tag] = $this->table_gposgsub($tag, $ttc_font);
			} elseif(trim($tag) == 'GDEF') {
				$this->Tables[$ttc_font][$tag] = $this->table_gdef($ttc_font);
			} elseif(trim($tag) == 'head') {
				$this->Tables[$ttc_font][$tag] = $this->table_head($ttc_font);
			} elseif(trim($tag) == 'maxp') {
				$this->Tables[$ttc_font][$tag] = $this->table_maxp($ttc_font);
			} elseif(trim($tag) == 'post') {
				$this->Tables[$ttc_font][$tag] = $this->table_post($ttc_font);
			} elseif(trim($tag) == 'OS/2') {
				$this->Tables[$ttc_font][$tag] = $this->table_os2($ttc_font);
			} elseif(trim($tag) == 'kern') {
				$this->Tables[$ttc_font][$tag] = $this->table_kern($ttc_font);
			} elseif(trim($tag) == 'feat') {
				$this->Tables[$ttc_font][$tag] = $this->table_feat($ttc_font);
			} elseif(trim($tag) == 'mort') {
				$this->Tables[$ttc_font][$tag] = $this->table_mort($ttc_font);
			} elseif(trim($tag) == 'morx') {
				$this->Tables[$ttc_font][$tag] = $this->table_morx($ttc_font);
			} elseif(trim($tag) == 'hhea') {
				$this->Tables[$ttc_font][$tag] = $this->table_hhea($ttc_font);
			} elseif(trim($tag) == 'hmtx') {
				$this->Tables[$ttc_font][$tag] = $this->table_hmtx($ttc_font);
			} else {
				$this->Tables[$ttc_font][$tag] = 'TODO';
			}
		}
		return $this->Tables[$ttc_font][$tag];

	}

	function table_name($ttc_font = 0) {
	# from http://www.microsoft.com/typography/otspec/name.htm
		fseek($this->fh, $this->TableDirectory[$ttc_font]['name']['offset']);
		$format = $this->read_USHORT();
		$count =  $this->read_USHORT();
		$stringOffset =  $this->read_USHORT();
		$nameRecords = array();
		for($i = 0; $i < $count; $i++) {
			$nameRecords[$i] = array('platformID' => $this->read_USHORT(), 'encodingID' => $this->read_USHORT(), 'languageID' => $this->read_USHORT(), 'nameID' => $this->read_USHORT(), 'length' => $this->read_USHORT(), 'offset' => $this->read_USHORT());
		}
		$langTagRecords = array();
		if($format == 1) {
			$langTagCount = $this->read_USHORT();
			for($i = 0; $i < $langTagCount; $i++) {
				$langTagRecords[$i] = array('length' => $this->read_USHORT(), 'offset' => $this->read_USHORT());
			}
		}
		for($i = 0; $i < count($langTagRecords); $i++) {
			fseek($this->fh, $this->TableDirectory[$ttc_font]['name']['offset']+$stringOffset+$langTagRecords[$i]['offset']);
			$data = fread($this->fh, $langTagRecords[$i]['length']);
			$langTagRecords[$i] = iconv('UTF-16BE','UTF-8',$data);
		}
		for($i = 0; $i < count($nameRecords); $i++) {
			fseek($this->fh, $this->TableDirectory[$ttc_font]['name']['offset']+$stringOffset+$nameRecords[$i]['offset']);
			if($nameRecords[$i]['length'] > 0) {
				$nameRecords[$i]['data'] = fread($this->fh, $nameRecords[$i]['length']);
			} else {
				$nameRecords[$i]['data'] = '';
			}
			unset($nameRecords[$i]['offset']);
			unset($nameRecords[$i]['length']);
		}
		return array('format' => $format, 'names' => $nameRecords, 'langTags' => $langTagRecords);
	}

	function table_cmap($ttc_font = 0) {
	# from http://www.microsoft.com/typography/otspec/cmap.htm
		fseek($this->fh, $this->TableDirectory[$ttc_font]['cmap']['offset']);
		$version = $this->read_USHORT();
		if($version != 0) return array();
		$numTables =  $this->read_USHORT();
		$encodingRecords = array();
		for($i = 0; $i < $numTables; $i++) {
			$encodingRecords[$i] = array('platformID' => $this->read_USHORT(), 'encodingID' => $this->read_USHORT(), 'offset' => $this->read_ULONG());
		}
		for($i = 0; $i < count($encodingRecords); $i++) {
			$subTableOffset = $this->TableDirectory[$ttc_font]['cmap']['offset']+$encodingRecords[$i]['offset'];
			fseek($this->fh, $subTableOffset);
			$format = $this->read_USHORT();
			switch($format) {
				case 0: # Format 0: Byte encoding table
					$encodingRecords[$i]['format'] = $format;
					$length = $this->read_USHORT();
					$encodingRecords[$i]['language'] = $this->read_USHORT();
					$encodingRecords[$i]['cmap'] = array_filter($this->read_BYTE(256));
					unset($encodingRecords[$i]['offset']);
					break;
				case 2: # Format 2: High-byte mapping through table
				# TO BE COMPLETED
					$encodingRecords[$i]['format'] = $format;
					$length = $this->read_USHORT();
					$encodingRecords[$i]['language'] = $this->read_USHORT();
					$subHeaderKeys = $this->read_USHORT(256);
					$subHeaders = array();
					foreach($subHeaderKeys as $key) {
						if($key > 0) {
							$firstCode =  $this->read_USHORT();
							$entryCount = $this->read_USHORT();
							$idDelta = $this->read_SHORT();
							$idRangeOffset = $this->read_USHORT();
							$pos = ftell($this->fh);
							fseek($this->fh, $pos - 2 + $idRangeOffset);
							$subHeaders[$key/8] = array('firstCode' => $firstCode, 'idDelta' => $idDelta, 'glyphIndexArray' => $this->read_USHORT($entryCount));
							fseek($this->fh, $pos);
						}
					}
					$firstCode =  $this->read_USHORT();
					$entryCount = $this->read_USHORT();
					$idDelta = $this->read_SHORT();
					$idRangeOffset = $this->read_USHORT();
					$subHeaders[0] = array('firstCode' => $firstCode, 'idDelta' => $idDelta, 'glyphIndexArray' => $this->read_USHORT($entryCount));
					unset($encodingRecords[$i]['offset']);
					$encodingRecords[$i]['subHeaderKeys'] = $subHeaderKeys;
					$encodingRecords[$i]['subHeaders'] = $subHeaders;
					$encodingRecords[$i]['cmap'] = "TODO";
					break;
				case 4: # Format 4: Segment mapping to delta values
					$encodingRecords[$i]['format'] = $format;
					$length = $this->read_USHORT();
					$encodingRecords[$i]['language'] = $this->read_USHORT();
					$segCountX2 = $this->read_USHORT();
					$segCount = $segCountX2 / 2;
					$searchRange = $this->read_USHORT();
					$entrySelector = $this->read_USHORT();
					$rangeShift = $this->read_USHORT();
					$endCount = $this->read_USHORT($segCount);
					$reservedPad = $this->read_USHORT();
					$startCount = $this->read_USHORT($segCount);
					$idDelta = $this->read_SHORT($segCount);
					$idRangeOffset = $this->read_USHORT($segCount);
					$leftover = $length - 16 - $segCount*8;
					$glyphIdArray = $this->read_USHORT($leftover/2);
					$cmap = array();
					for($j = 0; $j < $segCount; $j++) {
						for($c = $startCount[$j]; $c <= $endCount[$j]; $c++) {
							if($c != 0xFFFF) {
								if($idRangeOffset[$j] == 0) {
									$cmap[$c] = (($c + $idDelta[$j]) % 65536);
								} else {
									$o = ($idRangeOffset[$j]/2 + ($c - $startCount[$j])) - ($segCount - $j);
									$cmap[$c] = $glyphIdArray[$o];
								}
							}
						}
					}
					$encodingRecords[$i]['cmap'] = array_filter($cmap);
					unset($encodingRecords[$i]['offset']);
					break;
				case 6: # Format 6: Trimmed table mapping
					$encodingRecords[$i]['format'] = $format;
					$length = $this->read_USHORT();
					$encodingRecords[$i]['language'] = $this->read_USHORT();
					$firstCode = $this->read_USHORT();
					$entryCount = $this->read_USHORT();
					$glyphIdArray = $this->read_USHORT($entryCount);
					$cmap = array();
					for($j = 0; $j < $entryCount; $j++) {
						$cmap[$j + $firstCode] = $glyphIdArray[$j];
					}
					$encodingRecords[$i]['cmap'] = array_filter($cmap);
					unset($encodingRecords[$i]['offset']);
					break;
				case 8: # Format 8: mixed 16-bit and 32-bit coverage
				# TO BE COMPLETED
					$encodingRecords[$i]['format'] = $format.'.'.$this->read_USHORT();
					$length = $this->read_ULONG();
					$encodingRecords[$i]['language'] = $this->read_ULONG();
					$encodingRecords[$i]['cmap'] = "TODO";
					unset($encodingRecords[$i]['offset']);
					break;
				case 10: # Format 10: Trimmed array
				# TO BE COMPLETED
					$encodingRecords[$i]['format'] = $format.'.'.$this->read_USHORT();
					$length = $this->read_ULONG();
					$encodingRecords[$i]['language'] = $this->read_ULONG();
					$encodingRecords[$i]['cmap'] = "TODO";
					unset($encodingRecords[$i]['offset']);
					break;
				case 12: # Format 12: Segmented coverage
				# TO BE COMPLETED
					$encodingRecords[$i]['format'] = $format.'.'.$this->read_USHORT();
					$length = $this->read_ULONG();
					$encodingRecords[$i]['language'] = $this->read_ULONG();
					$encodingRecords[$i]['cmap'] = "TODO";
					unset($encodingRecords[$i]['offset']);
					break;
				case 13: # Format 13: Many-to-one range mappings
				# TO BE COMPLETED
					$encodingRecords[$i]['format'] = $format.'.'.$this->read_USHORT();
					$length = $this->read_ULONG();
					$encodingRecords[$i]['language'] = $this->read_ULONG();
					$encodingRecords[$i]['cmap'] = "TODO";
					unset($encodingRecords[$i]['offset']);
					break;
				case 14: # Format 14: Unicode Variation Sequences
				# TO BE COMPLETED
					$encodingRecords[$i]['format'] = $format;
					$length = $this->read_ULONG();
					$numVarSelectorRecords = $this->read_ULONG();
					$vsr = array();
					for($j = 0; $j < $numVarSelectorRecords; $j++) {
						$vsr[$j] = array();
						$vsr[$j]['varSelector'] = $this->read_UINT24();
						$vsr[$j]['defaultUVSOffset'] = $this->read_ULONG();
						$vsr[$j]['nonDefaultUVSOffset'] = $this->read_ULONG();
					}
					$varSelectorRecords = array();
					foreach($vsr as $v) {
						$duvs = array();
						if($v['defaultUVSOffset'] > 0) {
							fseek($this->fh, $subTableOffset + $v['defaultUVSOffset']);
							$numUnicodeValueRanges = $this->read_ULONG();
							for($j = 0; $j < $numUnicodeValueRanges; $j++) {
								$duvs[$j]['startUnicodeValue'] = $this->read_UINT24();
								$duvs[$j]['additionalCount'] = $this->read_BYTE();
							}	
						}
						$nduvs = array();
						if($v['nonDefaultUVSOffset'] > 0) {
							fseek($this->fh, $subTableOffset + $v['nonDefaultUVSOffset']);
							$numUVSMappings = $this->read_ULONG();
							for($j = 0; $j < $numUVSMappings; $j++) {
								$nduvs[$j]['unicodeValue'] = $this->read_UINT24();
								$nduvs[$j]['glyphID'] = $this->read_USHORT();
							}	
						}
						$varSelectorRecords[] = array('varSelector' => $v['varSelector'], 'defaultUVS' => $duvs, 'nonDefaultUVS' => $nduvs);
					}
					$encodingRecords[$i]['varSelectorRecords'] = $varSelectorRecords;
					$encodingRecords[$i]['cmap'] = "TODO";
					unset($encodingRecords[$i]['offset']);
					break;
			}
		}

		return $encodingRecords;
	}

	function table_gdef($ttc_font = 0) {
	# from http://www.microsoft.com/typography/otspec/GDEF.htm
		fseek($this->fh, $this->TableDirectory[$ttc_font]['GDEF']['offset']);
		$GDEF = array();
		$GDEF['Version'] = $this->read_USHORT().'.'.$this->read_USHORT();
		$GlyphClassDefOffset = $this->read_Offset();
		$AttachListOffset = $this->read_Offset();
		$LigCaretListOffset = $this->read_Offset();
		$MarkAttachClassDefOffset = $this->read_Offset();
		if($GDEF['Version'] == "1.2") {
			$MarkGlyphSetsDefOffset = $this->read_Offset();
		}
		if($GlyphClassDefOffset > 0) {
			$GDEF['GlyphClassDef'] = $this->read_Classes($this->TableDirectory[$ttc_font]['GDEF']['offset']+$GlyphClassDefOffset);
		}
		if($AttachListOffset > 0) {
			$AttachListOffset = $this->TableDirectory[$ttc_font]['GDEF']['offset']+$AttachListOffset;
			fseek($this->fh, $AttachListOffset);
			$CoverageOffset = $this->read_Offset();
			$GlyphCount = $this->read_uint16();
			$AttachPointOffsets = $this->read_Offset($GlyphCount);
			$Coverage = $this->read_Coverage($AttachListOffset+$CoverageOffset);
			$AttachPoints = array();
			for($i = 0; $i < $GlyphCount; $i++) {
				fseek($this->fh, $AttachListOffset+$AttachPointOffsets[$i]);
				$PointCount = $this->read_uint16();
				$AttachPoints[$Coverage[$i]] = $this->read_uint16($PointCount);
			}
			$GDEF['AttachList'] = $AttachPoints;
		}
		if($LigCaretListOffset > 0) {
			$LigCaretListOffset = $this->TableDirectory[$ttc_font]['GDEF']['offset']+$LigCaretListOffset;
			fseek($this->fh, $LigCaretListOffset);
			$CoverageOffset = $this->read_Offset();
			$LigGlyphCount = $this->read_uint16();
			$LigGlyphOffsets = $this->read_Offset($LigGlyphCount);
			$Coverage = $this->read_Coverage($LigCaretListOffset+$CoverageOffset);
			$LigCarets = array();
			for($i = 0; $i < $LigGlyphCount; $i++) {
				fseek($this->fh, $LigCaretListOffset+$LigGlyphOffsets[$i]);
				$CaretCount = $this->read_uint16();
				$CaretValueOffsets = $this->read_Offset($CaretCount);
				$CaretValues = array();
				foreach($CaretValueOffsets as $CaretValueOffset) {
					fseek($this->fh, $LigCaretListOffset+$LigGlyphOffsets[$i]+$CaretValueOffset);
					$CaretValueFormat = $this->read_uint16();
					switch($CaretValueFormat) {
						case 1: # Design units only
							$CaretValues[] = array('Format' => $CaretValueFormat, 'Coordinate' => $this->read_int16());
							break;
						case 2: # Contour point
							$CaretValues[] = array('Format' => $CaretValueFormat, 'ContourPoint' => $this->read_uint16());
							break;
						case 3: # Design units plus Device table
							$fpos = ftell($this->fh);
							$CaretValues[] = array('Format' => $CaretValueFormat, 'Coordinate' => $this->read_int16(), 'DeviceTable' => $this->read_DeviceTable($this->read_Offset()));
							fseek($this->fh, $fpos+4);
							break;
					}
				}
				$LigCarets[$Coverage[$i]] = $CaretValues;
			}
			$GDEF['LigCaretList'] = $LigCarets;
		}
		if($MarkAttachClassDefOffset > 0) {
			$GDEF['MarkAttachClassDef'] = $this->read_Classes($this->TableDirectory[$ttc_font]['GDEF']['offset']+$MarkAttachClassDefOffset);
		}
		if(isset($MarkGlyphSetsDefOffset) && $MarkGlyphSetsDefOffset > 0) {
			$MarkGlyphSetsDefOffset = $this->TableDirectory[$ttc_font]['GDEF']['offset']+$MarkGlyphSetsDefOffset;
			fseek($this->fh, $MarkGlyphSetsDefOffset);
			$MarkSetCount = $this->read_uint16();
			$MarkSetOffsets = $this->read_ULONG($MarkSetCount);
			$MarkSets = array();
			foreach($MarkSetOffsets as $MarkSetOffset) {
				$MarkSets = $this->read_Coverage($MarkGlyphSetsDefOffset+$MarkSetOffset);
			}
			$GDEF['MarkGlyphSetsDef'] = $MarkSets;
		}
		return $GDEF;
	}

	function read_DeviceTable($offset) {
	}

	function table_gposgsub($tag, $ttc_font = 0) {
	# from http://www.microsoft.com/typography/otspec/GPOS.htm
	# from http://www.microsoft.com/typography/otspec/GSUB.htm
		fseek($this->fh, $this->TableDirectory[$ttc_font][$tag]['offset']);
		$version = $this->read_Fixed();
		$ScriptListOffset = $this->TableDirectory[$ttc_font][$tag]['offset']+$this->read_Offset();
		$FeatureListOffset = $this->TableDirectory[$ttc_font][$tag]['offset']+$this->read_Offset();
		$LookupListOffset = $this->TableDirectory[$ttc_font][$tag]['offset']+$this->read_Offset();

		# Script List
		$ScriptList = array();
		fseek($this->fh, $ScriptListOffset);
		$ScriptCount = $this->read_uint16();
		$ScriptRecords = array();
		for($i = 0; $i < $ScriptCount; $i++) {
			$ScriptRecords[$this->read_Tag()] = $this->read_Offset(); # To be checked: can a script code appear more than once?
		}
		$ScriptTable = array();
		foreach($ScriptRecords as $ScriptTag => $ScriptOffset) {
			$ScriptList[$ScriptTag] = array();
			fseek($this->fh, $ScriptListOffset+$ScriptOffset);
			$DefaultLangSysOffset = $this->read_Offset();
			$LangSysCount = $this->read_uint16();
			$LangSysRecordOffsets = array();
			for($i = 0; $i < $LangSysCount; $i++) {
				$LangSysRecordOffsets[$this->read_Tag()] = $this->read_Offset();
			}
			$LangSysTable = array();
			if($DefaultLangSysOffset > 0) {
				fseek($this->fh, $ScriptListOffset+$ScriptOffset+$DefaultLangSysOffset);
				$LookupOrder = $this->read_Offset();
				$ReqFeatureIndex = $this->read_uint16();
				$FeatureCount = $this->read_uint16();
				$ScriptList[$ScriptTag]['dflt'] = array('ReqFeatureIndex' => $ReqFeatureIndex, 'FeatureIndices' => $this->read_uint16($FeatureCount));
			}
			foreach($LangSysRecordOffsets as $LangSysTag => $LangSysOffset) {
				fseek($this->fh, $ScriptListOffset+$ScriptOffset+$LangSysOffset);
				$LookupOrder = $this->read_Offset();
				$ReqFeatureIndex = $this->read_uint16();
				$FeatureCount = $this->read_uint16();
				$ScriptList[$ScriptTag][$LangSysTag] = array('ReqFeatureIndex' => $ReqFeatureIndex, 'FeatureIndices' => $this->read_uint16($FeatureCount));
			}
		}

		# Feature List
		$FeatureList = array();
		fseek($this->fh, $FeatureListOffset);
		$FeatureCount = $this->read_uint16();
		$FeatureRecords = array();
		for($i = 0; $i < $FeatureCount; $i++) {
			$FeatureRecords[] = array($this->read_Tag(),$this->read_Offset());
		}
		foreach($FeatureRecords as $frec) {
			fseek($this->fh, $FeatureListOffset+$frec[1]);
			$FeatureParams = $this->read_Offset();
			$LookupCount = $this->read_uint16();
			$FeatureList[] = array('FeatureTag' => $frec[0], 'LookupListIndices' => $this->read_uint16($LookupCount));
		}

		# Lookup List
		$LookupList = array();
		fseek($this->fh, $LookupListOffset);
		$LookupCount = $this->read_uint16();
		$LookupTableOffsets = $this->read_uint16($LookupCount);
		foreach($LookupTableOffsets as $LookupTableOffset) {
			fseek($this->fh, $LookupListOffset+$LookupTableOffset);
			$LookupType = $this->read_uint16();
			$LookupFlag = $this->read_uint16();
			$SubTableCount = $this->read_uint16();
			$SubTableOffsets = $this->read_uint16($SubTableCount);
			$MarkFilteringSet = $this->read_uint16();
			$SubTables = array();
			foreach($SubTableOffsets as $SubTableOffset) {
				$Subtable = $this->read_OTLookupTable($tag,$LookupType,$LookupListOffset+$LookupTableOffset+$SubTableOffset);
				if(isset($Subtable['ExtensionLookupType'])) {
					$ExtensionLookupType = $Subtable['ExtensionLookupType'];
					unset($Subtable['ExtensionLookupType']);
				}
				$SubTables[] = $Subtable;
			}
			if(isset($ExtensionLookupType)) {
				$LookupType = $ExtensionLookupType;
			}
			$LookupList[] = array('LookupType' => $LookupType, 'LookupFlag' => $LookupFlag, 'MarkFilteringSet' => $MarkFilteringSet, 'SubTables' => $SubTables);
		}
		return array('Version' => $version, 'ScriptList' => $ScriptList, 'FeatureList' => $FeatureList, 'LookupList' => $LookupList);
	}

	function read_OTLookupTable($tag, $type, $offset) {
		fseek($this->fh, $offset);
		switch($tag) {
			case 'GSUB':
				$SubstFormat = $this->read_uint16();
				switch($type) {
					case 1: # Single
						$CoverageOffset = $this->read_Offset();
						if($SubstFormat == 1) {
							$DeltaGlyphID = $this->read_uint16();
						} else {
							$GlyphCount = $this->read_uint16();
							$Substitute = $this->read_GlyphID($GlyphCount);
						}
						$Coverage = $this->read_Coverage($offset+$CoverageOffset);
						$Subst = array();
						if($SubstFormat == 1) {
							foreach($Coverage as $cv) {
								$Subst[$cv] = $cv+$DeltaGlyphID;
							}
						} else {
							for($i = 0; $i < $GlyphCount; $i++) {
								$Subst[$Coverage[$i]] = $Substitute[$i];
							}
						}
						return $Subst;
					case 2: # Multiple
						$CoverageOffset = $this->read_Offset();
						$SequenceCount = $this->read_uint16();
						$SequenceOffsets = $this->read_Offset($SequenceCount);
						$Coverage = $this->read_Coverage($offset+$CoverageOffset);
						$Sequences = array();
						foreach($SequenceOffsets as $SequenceOffset) {
							fseek($this->fh, $offset+$SequenceOffset);
							$GlyphCount = $this->read_uint16();
							$Sequences[] = $this->read_GlyphID($GlyphCount);
						}
						$Subst = array();
						for($i = 0; $i < $SequenceCount; $i++) {
							$Subst[$Coverage[$i]] = $Sequences[$i];
						}
						return $Subst;
					case 3: # Alternate
						$CoverageOffset = $this->read_Offset();
						$AlternateSetCount = $this->read_uint16();
						$AlternateSetOffsets = $this->read_Offset($AlternateSetCount);
						$Coverage = $this->read_Coverage($offset+$CoverageOffset);
						$AlternateSets = array();
						for($i = 0; $i < $AlternateSetCount; $i++) {
							fseek($this->fh, $offset+$AlternateSetOffsets[$i]);
							$GlyphCount = $this->read_uint16();
							$AlternateSets[$Coverage[$i]] = $this->read_GlyphID($GlyphCount);
						}
						return $AlternateSets;
					case 4: # Ligature
						$CoverageOffset = $this->read_Offset();
						$LigSetCount = $this->read_uint16();
						$LigSetOffsets = $this->read_Offset($LigSetCount);
						$Coverage = $this->read_Coverage($offset+$CoverageOffset);
						$LigatureSets = array();
						for($i = 0; $i < $LigSetCount; $i++) {
							fseek($this->fh, $offset+$LigSetOffsets[$i]);
							$LigatureCount = $this->read_uint16();
							$LigatureOffsets = $this->read_Offset($LigatureCount);
							$LigSets = array();
							foreach($LigatureOffsets as $LigatureOffset) {
								fseek($this->fh, $offset+$LigSetOffsets[$i]+$LigatureOffset);
								$LigGlyph = $this->read_GlyphID();
								$CompCount = $this->read_uint16();
								$Components = $this->read_GlyphID($CompCount-1);
								$LigSets[] = array('LigGlyph' => $LigGlyph,'Components' => $Components);
							}
							$LigatureSets[$Coverage[$i]] = $LigSets;
						}
						return $LigatureSets;
					case 5: # Context
						break;
					case 6: # Chaining Context
						if($SubstFormat == 1) {
							$CoverageOffset = $this->read_Offset();
							$ChainSubRuleSetCount = $this->read_uint16();
							$ChainSubRuleSetOffsets = $this->read_Offset($ChainSubRuleSetCount);
							$Coverage = $this->read_Coverage($offset+$CoverageOffset);
							$ChainSubRuleSets = array();
							foreach($ChainSubRuleSetOffsets as $ChainSubRuleSetOffset) {
								fseek($this->fh, $offset+$ChainSubRuleSetOffset);
								$ChainSubRuleCount = $this->read_uint16();
								$ChainSubRuleOffsets = $this->read_Offset($ChainSubRuleCount);
								$ChainSubRules = array();
								foreach($ChainSubRuleOffsets as $ChainSubRuleOffset) {
									fseek($this->fh, $offset+$ChainSubRuleSetOffset+$ChainSubRuleOffset);
									$BacktrackGlyphCount = $this->read_uint16();
									$Backtrack = $this->read_GlyphID($BacktrackGlyphCount);
									$InputGlyphCount = $this->read_uint16();
									$Input = $this->read_GlyphID($InputGlyphCount-1);
									$LookaheadGlyphCount = $this->read_uint16();
									$LookAhead = $this->read_GlyphID($LookaheadGlyphCount);
									$SubstCount = $this->read_uint16();
									$SubstLookupRecords = array();
									for($i = 0; $i < $SubstCount; $i++) {
										$SubstLookupRecords = array('SequenceIndex' => $this->read_uint16(), 'LookupListIndex' => $this->read_uint16());
									}
									$ChainSubRules[] = array('Backtrack' => $Backtrack, 'Input' => $Input, 'LookAhead' => $LookAhead, 'SubstLookupRecords' => $SubstLookupRecords);
								}
								$ChainSubRuleSets[] = $ChainSubRules;
							}
							return array('SubstFormat' => $SubstFormat, 'Coverage' => $Coverage, 'ChainSubRuleSets' => $ChainSubRuleSets);
						} elseif($SubstFormat == 2) {
							$CoverageOffset = $this->read_Offset();
							$BacktrackClassDefOffset = $this->read_Offset();
							$InputClassDefOffset = $this->read_Offset();
							$LookaheadClassDefOffset = $this->read_Offset();
							$ChainSubClassSetCnt = $this->read_uint16();
							$ChainSubClassSetOffsets = $this->read_Offset($ChainSubClassSetCnt);
							$Coverage = $this->read_Coverage($offset+$CoverageOffset);
							$BacktrackClassDef = $BacktrackClassDefOffset ? $this->read_Classes($offset+$BacktrackClassDefOffset) : array();
							$InputClassDef = $this->read_Classes($offset+$InputClassDefOffset);
							$LookaheadClassDef = $LookaheadClassDefOffset ? $this->read_Classes($offset+$LookaheadClassDefOffset) : array();
							$ChainSubClassSet = array();
							$c = 0;
							foreach($ChainSubClassSetOffsets as $ChainSubClassSetOffset) {
								if($ChainSubClassSetOffset > 0) {
									fseek($this->fh, $offset+$ChainSubClassSetOffset);
									$ChainSubClassRuleCnt = $this->read_uint16();
									$ChainSubClassRuleOffsets = $this->read_Offset($ChainSubClassRuleCnt);
									$ChainSubClassRules = array();
									foreach($ChainSubClassRuleOffsets as $ChainSubClassRuleOffset) {
										fseek($this->fh, $offset+$ChainSubClassSetOffset+$ChainSubClassRuleOffset);
										$BacktrackGlyphCount = $this->read_uint16();
										$Backtrack = $this->read_GlyphID($BacktrackGlyphCount);
										$InputGlyphCount = $this->read_uint16();
										$Input = $this->read_GlyphID($InputGlyphCount-1);
										$LookaheadGlyphCount = $this->read_uint16();
										$LookAhead = $this->read_GlyphID($LookaheadGlyphCount);
										$SubstCount = $this->read_uint16();
										$SubstLookupRecords = array();
										for($i = 0; $i < $SubstCount; $i++) {
											$SubstLookupRecords = array('SequenceIndex' => $this->read_uint16(), 'LookupListIndex' => $this->read_uint16());
										}
										$ChainSubClassRules[] = array('Backtrack' => $Backtrack, 'Input' => $Input, 'LookAhead' => $LookAhead, 'SubstLookupRecords' => $SubstLookupRecords);
									}
									$ChainSubClassSet[$c] = $ChainSubClassRules;
								}
								$c++;
							}
							return array('SubstFormat' => $SubstFormat, 'Coverage' => $Coverage, 'BacktrackClassDef' => $BacktrackClassDef, 'InputClassDef' => $InputClassDef, 'LookaheadClassDef' => $LookaheadClassDef, 'ChainSubClassSet' => $ChainSubClassSet);
						} elseif($SubstFormat == 3) {
							$BacktrackGlyphCount = $this->read_uint16();
							$BacktrackCoverageOffsets = $this->read_Offset($BacktrackGlyphCount);
							$InputGlyphCount = $this->read_uint16();
							$InputCoverageOffsets = $this->read_Offset($InputGlyphCount);
							$LookaheadGlyphCount = $this->read_uint16();
							$LookAheadCoverageOffsets = $this->read_Offset($LookaheadGlyphCount);
							$SubstCount = $this->read_uint16();
							$SubstLookupRecords = array();
							for($i = 0; $i < $SubstCount; $i++) {
								$SubstLookupRecords[] = array('SequenceIndex' => $this->read_uint16(), 'LookupListIndex' => $this->read_uint16());
							}
							$Backtrack = array();
							foreach($BacktrackCoverageOffsets as $BacktrackCoverageOffset) {
								$Backtrack[] = $this->read_Coverage($offset+$BacktrackCoverageOffset);
							}
							$Input = array();
							foreach($InputCoverageOffsets as $InputCoverageOffset) {
								$Input[] = $this->read_Coverage($offset+$InputCoverageOffset);
							}
							$LookAhead = array();
							foreach($LookAheadCoverageOffsets as $LookAheadCoverageOffset) {
								$LookAhead[] = $this->read_Coverage($offset+$LookAheadCoverageOffset);
							}
							return array('SubstFormat' => $SubstFormat, 'Backtrack' => $Backtrack, 'Input' => $Input, 'LookAhead' => $LookAhead, 'SubstLookupRecords' => $SubstLookupRecords);
						}
						break;
					case 7: # Extension Substitution
						$ExtensionLookupType = $this->read_USHORT();
						$ExtensionOffset = $this->read_ULONG();
						$Extension = $this->read_OTLookupTable($tag, $ExtensionLookupType, $offset+$ExtensionOffset);
						$Extension['ExtensionLookupType'] = $ExtensionLookupType;
						return $Extension;
					case 8: # Reverse chaining context single
						break;
				}
				return array('SubstFormat' => $SubstFormat);
			case 'GPOS':
				$PosFormat = $this->read_uint16();
				switch($type) {
					case 1: # Single adjustment
						break;
					case 2: # Pair adjustment
						break;
					case 3: # Cursive attachment
						break;
					case 4: # MarkToBase attachment
						$MarkCoverageOffset = $this->read_Offset();
						$BaseCoverageOffset = $this->read_Offset();
						$ClassCount = $this->read_uint16();
						$MarkArrayOffset = $this->read_Offset();
						$BaseArrayOffset = $this->read_Offset();
						$MarkCoverage = $this->read_Coverage($offset+$MarkCoverageOffset);
						$BaseCoverage = $this->read_Coverage($offset+$BaseCoverageOffset);
						//$MarkArray = $this->read_MarkArray($offset+$MarkArrayOffset);
						$BaseArray = array();
						fseek($this->fh, $offset+$BaseArrayOffset);
						$BaseCount = $this->read_uint16();
						$BaseRecordOffsets = array();
						for($i = 0; $i < $BaseCount; $i++) {
							$BaseRecordOffsets[] = $this->read_Offset($ClassCount);
						}
						
						break;
					case 5: # MarkToLigature attachment
						break;
					case 6: # MarkToMark attachment
						break;
					case 7: # Context positioning
						break;
					case 8: # Chained Context positioning
						break;
					case 9: # Extension positioning
						$ExtensionLookupType = $this->read_USHORT();
						$ExtensionOffset = $this->read_ULONG();
						$Extension = $this->read_OTLookupTable($tag, $ExtensionLookupType, $offset+$ExtensionOffset);
						$Extension['LookupType'] = $ExtensionLookupType;
						return $Extension;
				}
				return array('PosFormat' => $PosFormat);
		}
		return 'TODO';
	}

	function read_Coverage($offset) {
		fseek($this->fh, $offset);
		$CoverageFormat = $this->read_uint16();
		$Coverage = array();
		switch($CoverageFormat) {
			case 1:
				$GlyphCount = $this->read_uint16();
				for($i = 0; $i < $GlyphCount; $i++) {
					$Coverage[] = $this->read_GlyphID();
				}
				break;
			case 2:
				$RangeCount = $this->read_uint16();
				for($i = 0; $i < $RangeCount; $i++) {
					$Start = $this->read_GlyphID();
					$End = $this->read_GlyphID();
					$StartCoverageIndex = $this->read_uint16();
					for($j = $Start; $j <= $End; $j++) {
						$Coverage[] = $j;
					}
				}
				break;
		}
		return $Coverage;
	}

	function read_Classes($offset) {
		fseek($this->fh, $offset);
		$ClassFormat = $this->read_uint16();
		$Classes = array();
		switch($ClassFormat) {
			case 1:
				$StartGlyph = $this->read_GlyphID();
				$GlyphCount = $this->read_uint16();
				$Coverage = array();
				for($i = 0; $i < $GlyphCount; $i++) {
					$Classes[$StartGlyph+$i] = $this->read_GlyphID();
				}
				break;
			case 2:
				$RangeCount = $this->read_uint16();
				for($i = 0; $i < $RangeCount; $i++) {
					$Start = $this->read_GlyphID();
					$End = $this->read_GlyphID();
					$Class = $this->read_uint16();
					for($j = $Start; $j <= $End; $j++) {
						$Classes[$j] = $Class;
					}
				}
				break;
		}
		return $Classes;
	}

	function table_head($ttc_font = 0) {
	# from http://www.microsoft.com/typography/otspec/head.htm
		fseek($this->fh, $this->TableDirectory[$ttc_font]['head']['offset']);
		$head['version'] = $this->read_Fixed();
		$head['fontRevision'] = $this->read_Fixed();
		$head['checkSumAdjustment'] = $this->read_ULONG();
		$head['magicNumber'] = $this->read_ULONG();
		$head['flags'] = $this->read_USHORT();
		$head['unitsPerEm'] = $this->read_USHORT();
		$head['created'] = $this->read_LONGDATETIME();
		$head['modified'] = $this->read_LONGDATETIME();
		$head['xMin'] = $this->read_SHORT();
		$head['yMin'] = $this->read_SHORT();
		$head['xMax'] = $this->read_SHORT();
		$head['yMax'] = $this->read_SHORT();
		$head['macStyle'] = $this->read_USHORT();
		$head['lowestRecPPEM'] = $this->read_USHORT();
		$head['fontDirectionHint'] = $this->read_SHORT();
		$head['indexToLocFormat'] = $this->read_SHORT();
		$head['glyphDataFormat'] = $this->read_SHORT();
		return $head;
	}

	function table_maxp($ttc_font = 0) {
	# from http://www.microsoft.com/typography/otspec/maxp.htm
		fseek($this->fh, $this->TableDirectory[$ttc_font]['maxp']['offset']);
		$maxp['version'] = $this->read_Fixed();
		$maxp['numGlyphs'] = $this->read_USHORT();
		if($maxp['version'] == '1.0') {
			$maxp['maxPoints'] = $this->read_USHORT();
			$maxp['maxContours'] = $this->read_USHORT();
			$maxp['maxCompositePoints'] = $this->read_USHORT();
			$maxp['maxCompositeContours'] = $this->read_USHORT();
			$maxp['maxZones'] = $this->read_USHORT();
			$maxp['maxTwilightPoints'] = $this->read_USHORT();
			$maxp['maxStorage'] = $this->read_USHORT();
			$maxp['maxFunctionDefs'] = $this->read_USHORT();
			$maxp['maxInstructionDefs'] = $this->read_USHORT();
			$maxp['maxStackElements'] = $this->read_USHORT();
			$maxp['maxSizeOfInstructions'] = $this->read_USHORT();
			$maxp['maxComponentElements'] = $this->read_USHORT();
			$maxp['maxComponentDepth'] = $this->read_USHORT();
		}
		return $maxp;
	}

	function table_post($ttc_font = 0) {
	# from http://www.microsoft.com/typography/otspec/post.htm
		fseek($this->fh, $this->TableDirectory[$ttc_font]['post']['offset']);
		$post['version'] = $this->read_Fixed();
		$post['italicAngle'] = $this->read_Fixed();
		$post['underlinePosition'] = $this->read_FWord();
		$post['underlineThickness'] = $this->read_FWord();
		$post['isFixedPitch'] = $this->read_ULONG();
		$post['minMemType42'] = $this->read_ULONG();
		$post['maxMemType42'] = $this->read_ULONG();
		$post['minMemType1'] = $this->read_ULONG();
		$post['maxMemType1'] = $this->read_ULONG();
		$mac_glyph_names = array('.notdef','.null','nonmarkingreturn','space','exclam','quotedbl','numbersign','dollar','percent','ampersand','quotesingle','parenleft','parenright','asterisk','plus','comma','hyphen','period','slash','zero','one','two','three','four','five','six','seven','eight','nine','colon','semicolon','less','equal','greater','question','at','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','bracketleft','backslash','bracketright','asciicircum','underscore','grave','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','braceleft','bar','braceright','asciitilde','Adieresis','Aring','Ccedilla','Eacute','Ntilde','Odieresis','Udieresis','aacute','agrave','acircumflex','adieresis','atilde','aring','ccedilla','eacute','egrave','ecircumflex','edieresis','iacute','igrave','icircumflex','idieresis','ntilde','oacute','ograve','ocircumflex','odieresis','otilde','uacute','ugrave','ucircumflex','udieresis','dagger','degree','cent','sterling','section','bullet','paragraph','germandbls','registered','copyright','trademark','acute','dieresis','notequal','AE','Oslash','infinity','plusminus','lessequal','greaterequal','yen','mu','partialdiff','summation','product','pi','integral','ordfeminine','ordmasculine','Omega','ae','oslash','questiondown','exclamdown','logicalnot','radical','florin','approxequal','Delta','guillemotleft','guillemotright','ellipsis','nonbreakingspace','Agrave','Atilde','Otilde','OE','oe','endash','emdash','quotedblleft','quotedblright','quoteleft','quoteright','divide','lozenge','ydieresis','Ydieresis','fraction','currency','guilsinglleft','guilsinglright','fi','fl','daggerdbl','periodcentered','quotesinglbase','quotedblbase','perthousand','Acircumflex','Ecircumflex','Aacute','Edieresis','Egrave','Iacute','Icircumflex','Idieresis','Igrave','Oacute','Ocircumflex','apple','Ograve','Uacute','Ucircumflex','Ugrave','dotlessi','circumflex','tilde','macron','breve','dotaccent','ring','cedilla','hungarumlaut','ogonek','caron','Lslash','lslash','Scaron','scaron','Zcaron','zcaron','brokenbar','Eth','eth','Yacute','yacute','Thorn','thorn','minus','multiply','onesuperior','twosuperior','threesuperior','onehalf','onequarter','threequarters','franc','Gbreve','gbreve','Idotaccent','Scedilla','scedilla','Cacute','cacute','Ccaron','ccaron','dcroat');
		switch($post['version']) {
			case '1.0':
				$post['names'] = $mac_glyph_names;
				break;
			case '2.0':
				$numberOfGlyphs = $this->read_USHORT();
				$glyphNameIndex = $this->read_USHORT($numberOfGlyphs);
				$names = $this->read_PascalString(max(array_filter($glyphNameIndex, function($v) { return $v < 32768; }))-257);
				foreach($glyphNameIndex as $nid) {
					if($nid > 32767) {
						$post['names'][] = null; # reserved for future use
					} elseif($nid > 257) {
						$post['names'][] = $names[$nid-258];
					} else {
						$post['names'][] = $mac_glyph_names[$nid];
					}
				}
				break;
			case '2.5':
				$numberOfGlyphs = $this->read_USHORT();
				$offset = $this->read_CHAR($numberOfGlyphs);
				foreach($offset as $k => $v) {
					$post['names'][] = $mac_glyph_names[$k + $v];
				}
				break;
			case '3.0': # no PostScript name information
				$post['names'] = array();
				break;
			case '4.0':
				#echo (($this->TableDirectory[$ttc_font]['post']['length'] - 32)/2)."\n";
				$names = $this->read_uint16(($this->TableDirectory[$ttc_font]['post']['length'] - 32)/2);
				$post['names'] = array();
				foreach($names as $n) {
					if(isset($AdobeGlyphList[$n])) {
						$post['names'][] = $AdobeGlyphList[$n];
					} elseif($n == 0xFFFF) {
						$post['names'][] = null;
					} else {
						$post['names'][] = 'uni'.sprintf("%04X", $n);
					}
				}
				//$post['names'] = $names;
				break;
		}
		return $post;
	}

	function table_os2($ttc_font = 0) {
	# from http://www.microsoft.com/typography/otspec/os2.htm
		fseek($this->fh, $this->TableDirectory[$ttc_font]['OS/2']['offset']);
		$os2['version'] = $this->read_USHORT();
		$os2['xAvgCharWidth'] = $this->read_SHORT();
		$os2['usWeightClass'] = $this->read_USHORT();
		$os2['usWidthClass'] = $this->read_USHORT();
		$os2['fsType'] = $this->read_Flag(2);
		$os2['ySubscriptXSize'] = $this->read_SHORT();
		$os2['ySubscriptYSize'] = $this->read_SHORT();
		$os2['ySubscriptXOffset'] = $this->read_SHORT();
		$os2['ySubscriptYOffset'] = $this->read_SHORT();
		$os2['ySuperscriptXSize'] = $this->read_SHORT();
		$os2['ySuperscriptYSize'] = $this->read_SHORT();
		$os2['ySuperscriptXOffset'] = $this->read_SHORT();
		$os2['ySuperscriptYOffset'] = $this->read_SHORT();
		$os2['yStrikeoutSize'] = $this->read_SHORT();
		$os2['yStrikeoutPosition'] = $this->read_SHORT();
		$os2['sFamilyClass'] = $this->read_BYTE(2);
		$os2['panose']['bFamilyType'] = $this->read_BYTE();
		$os2['panose']['bSerifStyle'] = $this->read_BYTE();
		$os2['panose']['bWeight'] = $this->read_BYTE();
		$os2['panose']['bProportion'] = $this->read_BYTE();
		$os2['panose']['bContrast'] = $this->read_BYTE();
		$os2['panose']['bStrokeVariation'] = $this->read_BYTE();
		$os2['panose']['bArmStyle'] = $this->read_BYTE();
		$os2['panose']['bLetterform'] = $this->read_BYTE();
		$os2['panose']['bMidline'] = $this->read_BYTE();
		$os2['panose']['bXHeight'] = $this->read_BYTE();
		if($os2['version'] == 0) {
			# These should all be 0
			$os2['ulUnicodeRange'] = array_merge($this->read_Flag(4),$this->read_Flag(4),$this->read_Flag(4));
			$os2['ulCharRange'] = $this->read_Flag(4);
		} else {
			$os2['ulUnicodeRange'] = array_merge($this->read_Flag(4),$this->read_Flag(4),$this->read_Flag(4),$this->read_Flag(4));
		}
		$os2['achVendID'] = $this->read_Tag();
		$os2['fsSelection'] = $this->read_USHORT();
		$os2['usFirstCharIndex'] = $this->read_USHORT();
		$os2['usLastCharIndex'] = $this->read_USHORT();
		$os2['sTypoAscender'] = $this->read_SHORT();
		$os2['sTypoDescender'] = $this->read_SHORT();
		$os2['sTypoLineGap'] = $this->read_SHORT();
		$os2['usWinAscent'] = $this->read_USHORT();
		$os2['usWinDescent'] = $this->read_USHORT();
		if($os2['version'] > 0) {
			$os2['ulCodePageRange'] = array_merge($this->read_Flag(4),$this->read_Flag(4));
		}
		if($os2['version'] > 1) {
			$os2['sxHeight'] = $this->read_SHORT();
			$os2['sCapHeight'] = $this->read_SHORT();
			$os2['usDefaultChar'] = $this->read_USHORT();
			$os2['usBreakChar'] = $this->read_USHORT();
			$os2['usMaxContext'] = $this->read_USHORT();
		}
		if($os2['version'] > 4) {
			$os2['usLowerOpticalPointSize'] = $this->read_USHORT();
			$os2['usUpperOpticalPointSize'] = $this->read_USHORT();
		}
		return $os2;
	}

	function table_kern($ttc_font = 0) {
	# from http://www.microsoft.com/typography/otspec/kern.htm
	# from http://developer.apple.com/fonts/TTRefMan/RM06/Chap6kern.html
		fseek($this->fh, $this->TableDirectory[$ttc_font]['kern']['offset']);
		$version = $this->read_USHORT();
		if($version == 0) {
			$kern['version'] = $version;
			$kern['numTables'] =  $this->read_USHORT();
		} else {
			$version2 = $this->read_USHORT();
			$kern['version'] = $version.'.'.$version2;
			$kern['numTables'] =  $this->read_ULONG();
		}
		for($i = 0; $i < $kern['numTables']; $i++) {
			$length = $this->read_ULONG();
			$coverage = $this->read_BYTE();
			$format = $this->read_BYTE();
			$k = array('length' => $length, 'coverage' => $coverage, 'format' => $format);
			if($kern['version'] > 0) {
				$k['tupleIndex'] = $this->read_USHORT();
			}
			$kern['tables'][] = $k;
		}
		return $kern;
	}

	function table_feat($ttc_font = 0) {
	# from http://developer.apple.com/fonts/TTRefMan/RM06/Chap6feat.html
		fseek($this->fh, $this->TableDirectory[$ttc_font]['feat']['offset']);
		$version = $this->read_Fixed();
		$featureNameCount = $this->read_USHORT();
		$zero16 = $this->read_USHORT();
		$zero32 = $this->read_ULONG();
		$feat = array();
		for ($i = 0; $i < $featureNameCount; $i++) {
			$feature = $this->read_USHORT();
			$nSettings = $this->read_USHORT();
			$settingTableOffset = $this->read_ULONG();
			$featureFlags = $this->read_Flag(2);
			$nameIndex = $this->read_USHORT();
			$feat[$feature] = array('featureFlags' => $featureFlags, 'nameIndex' => $nameIndex);
			$fpos = ftell($this->fh);
			fseek($this->fh, $this->TableDirectory[$ttc_font]['feat']['offset'] + $settingTableOffset);
			for ($j = 0; $j < $nSettings; $j++) {
				$feat[$feature]['settings'][$this->read_USHORT()] = $this->read_USHORT();
			}
			fseek($this->fh, $fpos);
		}
		return array("version" => $version, "featureNames" => $feat);
	}

	function table_mort($ttc_font = 0) {
	# from http://developer.apple.com/fonts/TTRefMan/RM06/Chap6mort.html
		fseek($this->fh, $this->TableDirectory[$ttc_font]['mort']['offset']);
		$version = $this->read_Fixed();
		$nChains = $this->read_ULONG();
		$features = array();
		for ($i = 0; $i < $nChains; $i++) {
			$features[$i]['defaultFlags'] = $this->read_Flag(4);
			$features[$i]['chainLength'] = $this->read_ULONG();
			$nFeatureEntries = $this->read_USHORT();
			$features[$i]['nSubtables'] = $nSubtables = $this->read_USHORT();
			for ($j = 0; $j < $nFeatureEntries; $j++) {
				$features[$i]['featureEntries'][$j]['featureType'] = $this->read_USHORT();
				$features[$i]['featureEntries'][$j]['featureSetting'] = $this->read_USHORT();
				$features[$i]['featureEntries'][$j]['enableFlags'] =  $this->read_Flag(4);
				$features[$i]['featureEntries'][$j]['disableFlags'] =  $this->read_Flag(4);
			}
			for ($j = 0; $j < $nSubtables; $j++) {
				$features[$i]['subtables'][$j]['length'] = $length = $this->read_USHORT();
				$features[$i]['subtables'][$j]['coverage'] = $coverage = $this->read_Flag(2);
				$features[$i]['subtables'][$j]['subFeatureFlags'] = $this->read_Flag(4);
				#$coverage = str_split($coverage);
				$features[$i]['subtables'][$j]['verticalOnly'] = (int)$coverage[15];
				$features[$i]['subtables'][$j]['descendingOrder'] = (int)$coverage[14];
				$features[$i]['subtables'][$j]['orientationIndependant'] = (int)$coverage[13];
				$features[$i]['subtables'][$j]['subtableType'] = $subtableType = bindec($coverage[2].$coverage[1].$coverage[0]);
				switch($subtableType) {
					case 0: # Indic-style rearrangement
						break;
					case 1: # Contextual glyph substitution
						break;
					case 2: # Ligature substitution
						$features[$i]['subtables'][$j]['subtable']['stHeader'] = $this->read_StateHeader();
						$features[$i]['subtables'][$j]['subtable']['ligActionTable'] = $this->read_USHORT();
						$features[$i]['subtables'][$j]['subtable']['componentTable'] = $this->read_USHORT();
						$features[$i]['subtables'][$j]['subtable']['ligatureTable'] = $this->read_USHORT();
						break;
					case 4: # Non-contextual glyph substitution
						break;
					case 5: # Contextual glyph insertion
						break;
				}
			}
			return $features;
		}
	}

	function table_morx($ttc_font = 0) {
	# from http://developer.apple.com/fonts/TTRefMan/RM06/Chap6morx.html
		fseek($this->fh, $this->TableDirectory[$ttc_font]['morx']['offset']);
		$version = $this->read_Fixed();
		$nChains = $this->read_ULONG();
		$features = array();
		for ($i = 0; $i < $nChains; $i++) {
			$features[$i]['defaultFlags'] = $this->read_Flag(4);
			$features[$i]['chainLength'] = $this->read_ULONG();
			$nFeatureEntries = $this->read_ULONG();
			$features[$i]['nSubtables'] = $nSubtables = $this->read_ULONG();
			for ($j = 0; $j < $nFeatureEntries; $j++) {
				$features[$i]['featureEntries'][$j]['featureType'] = $this->read_USHORT();
				$features[$i]['featureEntries'][$j]['featureSetting'] = $this->read_USHORT();
				$features[$i]['featureEntries'][$j]['enableFlags'] =  $this->read_Flag(4);
				$features[$i]['featureEntries'][$j]['disableFlags'] =  $this->read_Flag(4);
			}
			$fpos = ftell($this->fh);
			for ($j = 0; $j < $nSubtables; $j++) {
				fseek($this->fh, $fpos);
				$features[$i]['subtables'][$j]['length'] = $length = $this->read_ULONG();
				$features[$i]['subtables'][$j]['coverage'] = $coverage = $this->read_Flag(4);
				$features[$i]['subtables'][$j]['subFeatureFlags'] = $this->read_Flag(4);
				$features[$i]['subtables'][$j]['verticalOnly'] = (int)$coverage[31];
				$features[$i]['subtables'][$j]['descendingOrder'] = (int)$coverage[30];
				$features[$i]['subtables'][$j]['orientationIndependent'] = (int)$coverage[29];
				$features[$i]['subtables'][$j]['subtableType'] = $subtableType = bindec($coverage[7].$coverage[6].$coverage[5].$coverage[4].$coverage[3].$coverage[2].$coverage[1].$coverage[0]);
				switch($subtableType) {
					case 0: # Indic-style rearrangement
						$STHpos = ftell($this->fh);
						$features[$i]['subtables'][$j]['subtable'] = $stHeader = $this->read_StateHeaderExtended();
						break;
					case 1: # Contextual glyph substitution
						break;
					case 2: # Ligature substitution
						$STHpos = ftell($this->fh);
						$features[$i]['subtables'][$j]['subtable'] = $this->read_StateHeaderExtended();
						$features[$i]['subtables'][$j]['subtable']['ligActionOffset'] = $this->read_ULONG();
						$features[$i]['subtables'][$j]['subtable']['componentOffset'] = $this->read_ULONG();
						$features[$i]['subtables'][$j]['subtable']['ligatureOffset'] = $this->read_ULONG();
						$stx = $features[$i]['subtables'][$j]['subtable'];
						fseek($this->fh, $STHpos + $stx['classTableOffset']);
						$features[$i]['subtables'][$j]['subtable']['classTable'] = $this->read_AATLookupTable();
						fseek($this->fh, $STHpos + $stx['stateArrayOffset']);
						fseek($this->fh, $STHpos + $stx['entryTableOffset']);
						fseek($this->fh, $STHpos + $stx['ligActionOffset']);
						fseek($this->fh, $STHpos + $stx['componentOffset']);
						fseek($this->fh, $STHpos + $stx['ligatureOffset']);
						break;
					case 4: # Non-contextual glyph substitution
						$features[$i]['subtables'][$j]['subtable'] = $this->read_AATLookupTable();
						break;
					case 5: # Contextual glyph insertion
						break;
				}
				$fpos += $length;
			}
			return $features;
		}
	}
	
	function table_hhea($ttc_font = 0) {
		fseek($this->fh, $this->TableDirectory[$ttc_font]['hhea']['offset']);		
		$hhea['version'] = $this->read_Fixed();
		$hhea['ascender'] = $this->read_SHORT(); // distance baseline of highest ascender
		$hhea['descender'] = $this->read_SHORT(); // distance baseline of lowest descender
		$hhea['lineGap'] = $this->read_SHORT();
		$hhea['advanceWidthMax'] = $this->read_USHORT();
		$hhea['minLeftSideBearing'] = $this->read_SHORT();
		$hhea['minRightSideBearing'] = $this->read_SHORT();
		$hhea['xMaxExtent'] = $this->read_SHORT();
		$hhea['caretSlopeRise'] = $this->read_SHORT();
		$hhea['caretSlopeRun'] = $this->read_SHORT(); // 0 for vertical
		$hhea['caretOffset'] = $this->read_SHORT(); // 0 for non-slanted		
		$hhea['reserved0'] = $this->read_SHORT();
		$hhea['reserved1'] = $this->read_SHORT();
		$hhea['reserved2'] = $this->read_SHORT();
		$hhea['reserved3'] = $this->read_SHORT();
		$hhea['metricDataFormat'] = $this->read_SHORT();
		$hhea['numberOfHMetrics'] = $this->read_USHORT(); // no of hMetric entries in hmtx table
		return $hhea;
	}
	
	function table_hmtx($ttc_font = 0) {
		# todo: add somekind of cache for recurring (private) variables like numGlyphs
		$_post = $this->table('post', $ttc_font);	
		$_maxp = $this->table('maxp', $ttc_font);						
		$_numGlyphs = $_maxp['numGlyphs'];
		$_hhea = $this->table('hhea', $ttc_font);						
		$_numberOfHMetrics = $_hhea['numberOfHMetrics'];
		fseek($this->fh, $this->TableDirectory[$ttc_font]['hmtx']['offset']);
		$hmtx = array();
		for($i=0; $i<$_numberOfHMetrics; $i++) {
			$hmtx[$i]['name'] = $_post['names'][$i];
			$hmtx[$i]['advanceWidth'] = $this->read_USHORT();
			$hmtx[$i]['leftSideBearing'] = $this->read_SHORT();	
		}
		return $hmtx;
	}
		

/*******************************************************************************
 *******************************************************************************
 *******************************************************************************
 **********          TO BE MOVED TO sfnt.utils.php                  ************
 *******************************************************************************
 *******************************************************************************
 *******************************************************************************/

	function txt_ulUnicodeRange($ulUnicodeRange) {
		$ulUnicodeRange_str = array();
		if ($ulUnicodeRange[0] == 1) {
			$ulUnicodeRange_str[] = 'Basic Latin';
		}
		if ($ulUnicodeRange[1] == 1) {
			$ulUnicodeRange_str[] = 'Latin-1 Supplement';
		}
		if ($ulUnicodeRange[2] == 1) {
			$ulUnicodeRange_str[] = 'Latin Extended-A';
		}
		if ($ulUnicodeRange[3] == 1) {
			$ulUnicodeRange_str[] = 'Latin Extended-B';
		}
		if ($ulUnicodeRange[4] == 1) {
			$ulUnicodeRange_str[] = 'IPA Extensions';
		}
		if ($ulUnicodeRange[5] == 1) {
			$ulUnicodeRange_str[] = 'Spacing Modifier Letters';
		}
		if ($ulUnicodeRange[6] == 1) {
			$ulUnicodeRange_str[] = 'Combining Diacritical Marks';
		}
		if ($ulUnicodeRange[7] == 1) {
			$ulUnicodeRange_str[] = 'Greek and Coptic';
		}
		if ($ulUnicodeRange[8] == 1) {
			$ulUnicodeRange_str[] = 'Reserved for Unicode SubRanges';
		}
		if ($ulUnicodeRange[9] == 1) {
			$ulUnicodeRange_str[] = 'Cyrillic';
			$ulUnicodeRange_str[] = 'Cyrillic Supplementary';
		}
		if ($ulUnicodeRange[10] == 1) {
			$ulUnicodeRange_str[] = 'Armenian';
		}
		if ($ulUnicodeRange[11] == 1) {
			$ulUnicodeRange_str[] = 'Hebrew';
		}
		if ($ulUnicodeRange[12] == 1) {
			$ulUnicodeRange_str[] = 'Reserved for Unicode SubRanges';
		}
		if ($ulUnicodeRange[13] == 1) {
			$ulUnicodeRange_str[] = 'Arabic';
		}
		if ($ulUnicodeRange[14] == 1) {
			$ulUnicodeRange_str[] = 'Reserved for Unicode SubRanges';
		}
		if ($ulUnicodeRange[15] == 1) {
			$ulUnicodeRange_str[] = 'Devanagari';
		}
		if ($ulUnicodeRange[16] == 1) {
			$ulUnicodeRange_str[] = 'Bengali';
		}
		if ($ulUnicodeRange[17] == 1) {
			$ulUnicodeRange_str[] = 'Gurmukhi';
		}
		if ($ulUnicodeRange[18] == 1) {
			$ulUnicodeRange_str[] = 'Gujarati';
		}
		if ($ulUnicodeRange[19] == 1) {
			$ulUnicodeRange_str[] = 'Oriya';
		}
		if ($ulUnicodeRange[20] == 1) {
			$ulUnicodeRange_str[] = 'Tamil';
		}
		if ($ulUnicodeRange[21] == 1) {
			$ulUnicodeRange_str[] = 'Telugu';
		}
		if ($ulUnicodeRange[22] == 1) {
			$ulUnicodeRange_str[] = 'Kannada';
		}
		if ($ulUnicodeRange[23] == 1) {
			$ulUnicodeRange_str[] = 'Malayalam';
		}
		if ($ulUnicodeRange[24] == 1) {
			$ulUnicodeRange_str[] = 'Thai';
		}
		if ($ulUnicodeRange[25] == 1) {
			$ulUnicodeRange_str[] = 'Lao';
		}
		if ($ulUnicodeRange[26] == 1) {
			$ulUnicodeRange_str[] = 'Georgian';
		}
		if ($ulUnicodeRange[27] == 1) {
			$ulUnicodeRange_str[] = 'Reserved for Unicode SubRanges';
		}
		if ($ulUnicodeRange[28] == 1) {
			$ulUnicodeRange_str[] = 'Hangul Jamo';
		}
		if ($ulUnicodeRange[29] == 1) {
			$ulUnicodeRange_str[] = 'Latin Extended Additional';
		}
		if ($ulUnicodeRange[30] == 1) {
			$ulUnicodeRange_str[] = 'Greek Extended';
		}
		if ($ulUnicodeRange[31] == 1) {
			$ulUnicodeRange_str[] = 'General Punctuation';
		}
		if ($ulUnicodeRange[32] == 1) {
			$ulUnicodeRange_str[] = 'Superscripts And Subscripts';
		}
		if ($ulUnicodeRange[33] == 1) {
			$ulUnicodeRange_str[] = 'Currency Symbols';
		}
		if ($ulUnicodeRange[34] == 1) {
			$ulUnicodeRange_str[] = 'Combining Diacritical Marks For Symbols';
		}
		if ($ulUnicodeRange[35] == 1) {
			$ulUnicodeRange_str[] = 'Letterlike Symbols';
		}
		if ($ulUnicodeRange[36] == 1) {
			$ulUnicodeRange_str[] = 'Number Forms';
		}
		if ($ulUnicodeRange[37] == 1) {
			$ulUnicodeRange_str[] = 'Arrows';
			$ulUnicodeRange_str[] = 'Supplemental Arrows-A';
			$ulUnicodeRange_str[] = 'Supplemental Arrows-B';
		}
		if ($ulUnicodeRange[38] == 1) {
			$ulUnicodeRange_str[] = 'Mathematical Operators';
			$ulUnicodeRange_str[] = 'Supplemental Mathematical Operators';
			$ulUnicodeRange_str[] = 'Miscellaneous Mathematical Symbols-A';
			$ulUnicodeRange_str[] = 'Miscellaneous Mathematical Symbols-B';
		}
		if ($ulUnicodeRange[39] == 1) {
			$ulUnicodeRange_str[] = 'Miscellaneous Technical';
		}
		if ($ulUnicodeRange[40] == 1) {
			$ulUnicodeRange_str[] = 'Control Pictures';
		}
		if ($ulUnicodeRange[41] == 1) {
			$ulUnicodeRange_str[] = 'Optical Character Recognition';
		}
		if ($ulUnicodeRange[42] == 1) {
			$ulUnicodeRange_str[] = 'Enclosed Alphanumerics';
		}
		if ($ulUnicodeRange[43] == 1) {
			$ulUnicodeRange_str[] = 'Box Drawing';
		}
		if ($ulUnicodeRange[44] == 1) {
			$ulUnicodeRange_str[] = 'Block Elements';
		}
		if ($ulUnicodeRange[45] == 1) {
			$ulUnicodeRange_str[] = 'Geometric Shapes';
		}
		if ($ulUnicodeRange[46] == 1) {
			$ulUnicodeRange_str[] = 'Miscellaneous Symbols';
		}
		if ($ulUnicodeRange[47] == 1) {
			$ulUnicodeRange_str[] = 'Dingbats';
		}
		if ($ulUnicodeRange[48] == 1) {
			$ulUnicodeRange_str[] = 'CJK Symbols And Punctuation';
		}
		if ($ulUnicodeRange[49] == 1) {
			$ulUnicodeRange_str[] = 'Hiragana';
		}
		if ($ulUnicodeRange[50] == 1) {
			$ulUnicodeRange_str[] = 'Katakana';
			$ulUnicodeRange_str[] = 'Katakana Phonetic Extensions';
		}
		if ($ulUnicodeRange[51] == 1) {
			$ulUnicodeRange_str[] = 'Bopomofo';
			$ulUnicodeRange_str[] = 'Bopomofo Extended';
		}
		if ($ulUnicodeRange[52] == 1) {
			$ulUnicodeRange_str[] = 'Hangul Compatibility Jamo';
		}
		if ($ulUnicodeRange[53] == 1) {
			$ulUnicodeRange_str[] = 'Reserved for Unicode SubRanges';
		}
		if ($ulUnicodeRange[54] == 1) {
			$ulUnicodeRange_str[] = 'Enclosed CJK Letters And Months';
		}
		if ($ulUnicodeRange[55] == 1) {
			$ulUnicodeRange_str[] = 'CJK Compatibility';
		}
		if ($ulUnicodeRange[56] == 1) {
			$ulUnicodeRange_str[] = 'Hangul Syllables';
		}
		if ($ulUnicodeRange[57] == 1) {
			$ulUnicodeRange_str[] = 'Non-Plane 0 *';
		}
		if ($ulUnicodeRange[58] == 1) {
			$ulUnicodeRange_str[] = 'Reserved for Unicode SubRanges';
		}
		if ($ulUnicodeRange[59] == 1) {
			$ulUnicodeRange_str[] = 'CJK Unified Ideographs';
			$ulUnicodeRange_str[] = 'CJK Radicals Supplement';
			$ulUnicodeRange_str[] = 'Kangxi Radicals';
			$ulUnicodeRange_str[] = 'Ideographic Description Characters';
			$ulUnicodeRange_str[] = 'CJK Unified Ideograph Extension A';
			$ulUnicodeRange_str[] = 'CJK Unified Ideographs Extension B';
			$ulUnicodeRange_str[] = 'Kanbun';
		}
		if ($ulUnicodeRange[60] == 1) {
			$ulUnicodeRange_str[] = 'Private Use Area';
		}
		if ($ulUnicodeRange[61] == 1) {
			$ulUnicodeRange_str[] = 'CJK Compatibility Ideographs';
			$ulUnicodeRange_str[] = 'CJK Compatibility Ideographs Supplement';
		}
		if ($ulUnicodeRange[62] == 1) {
			$ulUnicodeRange_str[] = 'Alphabetic Presentation Forms';
		}
		if ($ulUnicodeRange[63] == 1) {
			$ulUnicodeRange_str[] = 'Arabic Presentation Forms-A';
		}
		if ($ulUnicodeRange[64] == 1) {
			$ulUnicodeRange_str[] = 'Combining Half Marks';
		}
		if ($ulUnicodeRange[65] == 1) {
			$ulUnicodeRange_str[] = 'CJK Compatibility Forms';
		}
		if ($ulUnicodeRange[66] == 1) {
			$ulUnicodeRange_str[] = 'Small Form Variants';
		}
		if ($ulUnicodeRange[67] == 1) {
			$ulUnicodeRange_str[] = 'Arabic Presentation Forms-B';
		}
		if ($ulUnicodeRange[68] == 1) {
			$ulUnicodeRange_str[] = 'Halfwidth And Fullwidth Forms';
		}
		if ($ulUnicodeRange[69] == 1) {
			$ulUnicodeRange_str[] = 'Specials';
		}
		if ($ulUnicodeRange[70] == 1) {
			$ulUnicodeRange_str[] = 'Tibetan';
		}
		if ($ulUnicodeRange[71] == 1) {
			$ulUnicodeRange_str[] = 'Syriac';
		}
		if ($ulUnicodeRange[72] == 1) {
			$ulUnicodeRange_str[] = 'Thaana';
		}
		if ($ulUnicodeRange[73] == 1) {
			$ulUnicodeRange_str[] = 'Sinhala';
		}
		if ($ulUnicodeRange[74] == 1) {
			$ulUnicodeRange_str[] = 'Myanmar';
		}
		if ($ulUnicodeRange[75] == 1) {
			$ulUnicodeRange_str[] = 'Ethiopic';
		}
		if ($ulUnicodeRange[76] == 1) {
			$ulUnicodeRange_str[] = 'Cherokee';
		}
		if ($ulUnicodeRange[77] == 1) {
			$ulUnicodeRange_str[] = 'Unified Canadian Aboriginal Syllabics';
		}
		if ($ulUnicodeRange[78] == 1) {
			$ulUnicodeRange_str[] = 'Ogham';
		}
		if ($ulUnicodeRange[79] == 1) {
			$ulUnicodeRange_str[] = 'Runic';
		}
		if ($ulUnicodeRange[80] == 1) {
			$ulUnicodeRange_str[] = 'Khmer';
		}
		if ($ulUnicodeRange[81] == 1) {
			$ulUnicodeRange_str[] = 'Mongolian';
		}
		if ($ulUnicodeRange[82] == 1) {
			$ulUnicodeRange_str[] = 'Braille Patterns';
		}
		if ($ulUnicodeRange[83] == 1) {
			$ulUnicodeRange_str[] = 'Yi Syllables';
			$ulUnicodeRange_str[] = 'Yi Radicals';
		}
		if ($ulUnicodeRange[84] == 1) {
			$ulUnicodeRange_str[] = 'Tagalog';
			$ulUnicodeRange_str[] = 'Hanunoo';
			$ulUnicodeRange_str[] = 'Buhid';
			$ulUnicodeRange_str[] = 'Tagbanwa';
		}
		if ($ulUnicodeRange[85] == 1) {
			$ulUnicodeRange_str[] = 'Old Italic';
		}
		if ($ulUnicodeRange[86] == 1) {
			$ulUnicodeRange_str[] = 'Gothic';
		}
		if ($ulUnicodeRange[87] == 1) {
			$ulUnicodeRange_str[] = 'Deseret';
		}
		if ($ulUnicodeRange[88] == 1) {
			$ulUnicodeRange_str[] = 'Byzantine Musical Symbols';
			$ulUnicodeRange_str[] = 'Musical Symbols';
		}
		if ($ulUnicodeRange[89] == 1) {
			$ulUnicodeRange_str[] = 'Mathematical Alphanumeric Symbols';
		}
		if ($ulUnicodeRange[90] == 1) {
			$ulUnicodeRange_str[] = 'Private Use (plane 15)';
			$ulUnicodeRange_str[] = 'Private Use (plane 16)';
		}
		if ($ulUnicodeRange[91] == 1) {
			$ulUnicodeRange_str[] = 'Variation Selectors';
		}
		if ($ulUnicodeRange[92] == 1) {
			$ulUnicodeRange_str[] = 'Tags';
		}
		return $ulUnicodeRange_str;
	}

	function txt_ulCodePageRange($ulCodePageRange) {
		$ulCodePageRange_str = array();
		if ($ulCodePageRange[0] == 1) {
			$ulCodePageRange_str[] = 'Latin 1 (cp1252)';
		}
		if ($ulCodePageRange[1] == 1) {
			$ulCodePageRange_str[] = 'Latin 2: Eastern Europe (cp1250)';
		}
		if ($ulCodePageRange[2] == 1) {
			$ulCodePageRange_str[] = 'Cyrillic (cp1251)';
		}
		if ($ulCodePageRange[3] == 1) {
			$ulCodePageRange_str[] = 'Greek (cp1253)';
		}
		if ($ulCodePageRange[4] == 1) {
			$ulCodePageRange_str[] = 'Turkish (cp1254)';
		}
		if ($ulCodePageRange[5] == 1) {
			$ulCodePageRange_str[] = 'Hebrew (cp1255)';
		}
		if ($ulCodePageRange[6] == 1) {
			$ulCodePageRange_str[] = 'Arabic (cp1256)';
		}
		if ($ulCodePageRange[7] == 1) {
			$ulCodePageRange_str[] = 'Windows Baltic (cp1257)';
		}
		if ($ulCodePageRange[8] == 1) {
			$ulCodePageRange_str[] = 'Vietnamese (cp1258)';
		}
		if ($ulCodePageRange[9] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI';
		}
		if ($ulCodePageRange[10] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI';
		}
		if ($ulCodePageRange[11] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI';
		}
		if ($ulCodePageRange[12] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI';
		}
		if ($ulCodePageRange[13] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI';
		}
		if ($ulCodePageRange[14] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI';
		}
		if ($ulCodePageRange[15] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI';
		}
		if ($ulCodePageRange[16] == 1) {
			$ulCodePageRange_str[] = 'Thai (cp874)';
		}
		if ($ulCodePageRange[17] == 1) {
			$ulCodePageRange_str[] = 'JIS/Japan (cp932)';
		}
		if ($ulCodePageRange[18] == 1) {
			$ulCodePageRange_str[] = 'Chinese: Simplified chars--PRC and Singapore (cp936)';
		}
		if ($ulCodePageRange[19] == 1) {
			$ulCodePageRange_str[] = 'Korean Wansung (cp949)';
		}
		if ($ulCodePageRange[20] == 1) {
			$ulCodePageRange_str[] = 'Chinese: Traditional chars--Taiwan and Hong Kong (cp950)';
		}
		if ($ulCodePageRange[21] == 1) {
			$ulCodePageRange_str[] = 'Korean Johab (cp1361)';
		}
		if ($ulCodePageRange[22] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI & OEM';
		}
		if ($ulCodePageRange[23] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI & OEM';
		}
		if ($ulCodePageRange[24] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI & OEM';
		}
		if ($ulCodePageRange[25] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI & OEM';
		}
		if ($ulCodePageRange[26] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI & OEM';
		}
		if ($ulCodePageRange[27] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI & OEM';
		}
		if ($ulCodePageRange[28] == 1) {
			$ulCodePageRange_str[] = 'Alternate ANSI & OEM';
		}
		if ($ulCodePageRange[29] == 1) {
			$ulCodePageRange_str[] = 'Macintosh Character Set (US Roman)';
		}
		if ($ulCodePageRange[30] == 1) {
			$ulCodePageRange_str[] = 'OEM Character Set';
		}
		if ($ulCodePageRange[31] == 1) {
			$ulCodePageRange_str[] = 'Symbol Character Set';
		}
		if ($ulCodePageRange[32] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[33] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[34] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[35] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[36] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[37] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[38] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[39] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[40] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[41] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[42] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[43] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[44] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[45] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[46] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[47] == 1) {
			$ulCodePageRange_str[] = 'OEM';
		}
		if ($ulCodePageRange[48] == 1) {
			$ulCodePageRange_str[] = 'IBM Greek (cp869)';
		}
		if ($ulCodePageRange[49] == 1) {
			$ulCodePageRange_str[] = 'MS-DOS Russian (cp866)';
		}
		if ($ulCodePageRange[50] == 1) {
			$ulCodePageRange_str[] = 'MS-DOS Nordic (cp865)';
		}
		if ($ulCodePageRange[51] == 1) {
			$ulCodePageRange_str[] = 'Arabic (cp864)';
		}
		if ($ulCodePageRange[52] == 1) {
			$ulCodePageRange_str[] = 'MS-DOS Canadian French (cp863)';
		}
		if ($ulCodePageRange[53] == 1) {
			$ulCodePageRange_str[] = 'Hebrew (cp862)';
		}
		if ($ulCodePageRange[54] == 1) {
			$ulCodePageRange_str[] = 'MS-DOS Icelandic (cp861)';
		}
		if ($ulCodePageRange[55] == 1) {
			$ulCodePageRange_str[] = 'MS-DOS Portuguese (cp860)';
		}
		if ($ulCodePageRange[56] == 1) {
			$ulCodePageRange_str[] = 'IBM Turkish (cp857)';
		}
		if ($ulCodePageRange[57] == 1) {
			$ulCodePageRange_str[] = 'IBM Cyrillic; primarily Russian (cp855)';
		}
		if ($ulCodePageRange[58] == 1) {
			$ulCodePageRange_str[] = 'Latin 2 (cp852)';
		}
		if ($ulCodePageRange[59] == 1) {
			$ulCodePageRange_str[] = 'MS-DOS Baltic (cp775)';
		}
		if ($ulCodePageRange[60] == 1) {
			$ulCodePageRange_str[] = 'Greek; former 437 G (cp737)';
		}
		if ($ulCodePageRange[61] == 1) {
			$ulCodePageRange_str[] = 'Arabic; ASMO 708 (cp708)';
		}
		if ($ulCodePageRange[62] == 1) {
			$ulCodePageRange_str[] = 'WE/Latin 1 (cp850)';
		}
		if ($ulCodePageRange[63] == 1) {
			$ulCodePageRange_str[] = 'US (cp437)';
		}
		return $ulCodePageRange_str;
	}

	function txt_os2_fsType($fsType) {
		$fsType_str = array();
		if ($fsType[1] == 1) {
			$fsType_str[] = 'Restricted License embedding';
		}
		if ($fsType[2] == 1) {
			$fsType_str[] = 'Preview & Print embedding';
		}
		if ($fsType[3] == 1) {
			$fsType_str[] = 'Editable embedding';
		}
		if ($fsType[8] == 1) {
			$fsType_str[] = 'No subsetting';
		}
		if ($fsType[9] == 1) {
			$fsType_str[] = 'Bitmap embedding only';
		}
		if (empty($fsType_str)) {
			$fsType_str[] = 'Installable Embedding';
		}
		return $fsType_str;
	}

	function txt_os2_fsSelection($binstr) {
		$fsSelection = str_split($binstr);
		$fsSelection_str = array();
		if ($fsSelection[0] == 1) {
			$fsSelection_str[] = 'Italic';
		}
		if ($fsSelection[1] == 1) {
			$fsSelection_str[] = 'Underscore';
		}
		if ($fsSelection[2] == 1) {
			$fsSelection_str[] = 'Negative';
		}
		if ($fsSelection[3] == 1) {
			$fsSelection_str[] = 'Outlined';
		}
		if ($fsSelection[4] == 1) {
			$fsSelection_str[] = 'Strikeout';
		}
		if ($fsSelection[5] == 1) {
			$fsSelection_str[] = 'Bold';
		}
		if ($fsSelection[6] == 1) {
			$fsSelection_str[] = 'Regular';
		}
		return $fsSelection_str;
	}

	function txt_platformID($id) {
		$platform = array(0 =>'Unicode',1 =>'Macintosh',2 =>'ISO',3 =>'Microsoft',4 =>'Custom');
		if(array_key_exists($id,$platform)) {
			return $platform[$id];
		} else {
			return 'undocumented platformID ('.$id.')';
		}
	}

	function txt_encodingID_Uni($id) {
		$encoding = array(0 =>'Unicode 1.0 semantics',1 =>'Unicode 1.1 semantics',2 =>'ISO 10646:1993 semantics',3 =>'Unicode 2.0 and onwards semantics, Unicode BMP only.',4 =>'Unicode 2.0 and onwards semantics, Unicode full repertoire.');
		if(array_key_exists($id,$encoding)) {
			return $encoding[$id];
		} else {
			return 'undocumented encodingID ('.$id.')';
		}
	}

	function txt_encodingID_Mac($id) {
		$encoding = array(0 =>'Roman',1 =>'Japanese',2 =>'Chinese (Traditional)',3 =>'Korean',4 =>'Arabic',5 =>'Hebrew',6 =>'Greek',7 =>'Russian',8 =>'RSymbol',9 =>'Devanagari',10 =>'Gurmukhi',11 =>'Gujarati',12 =>'Oriya',13 =>'Bengali',14 =>'Tamil',15 =>'Telugu',16 =>'Kannada',17 =>'Malayalam',18 =>'Sinhalese',19 =>'Burmese',20 =>'Khmer',21 =>'Thai',22 =>'Laotian',23 =>'Georgian',24 =>'Armenian',25 =>'Chinese (Simplified)',26 =>'Tibetan',27 =>'Mongolian',28 =>'Geez',29 =>'Slavic',30 =>'Vietnamese',31 =>'Sindhi',32 =>'Uninterpreted');
		if(array_key_exists($id,$encoding)) {
			return $encoding[$id];
		} else {
			return 'undocumented encodingID ('.$id.')';
		}
	}

	function txt_encodingID_ISO($id) {
		$encoding = array(0 =>'7-bit ASCII',1 =>'ISO 10646',2 =>'ISO 8859-1');
		if(array_key_exists($id,$encoding)) {
			return $encoding[$id];
		} else {
			return 'undocumented encodingID ('.$id.')';
		}
	}

	function txt_encodingID_MS($id) {
		$encoding = array(0 => 'Symbol',1 => 'Unicode BMP only',2 => 'ShiftJIS',3 => 'PRC',4 => 'Big5',5 => 'Wansung',6 => 'Johab',7 => 'Reserved',8 => 'Reserved',9 => 'Reserved',10 => 'Unicode full repertoire');
		if(array_key_exists($id,$encoding)) {
			return $encoding[$id];
		} else {
			return 'undocumented encodingID ('.$id.')';
		}
	}

	function txt_languageID_Mac($id) {
		$language = array(0 => 'English',1 => 'French',2 => 'German',3 => 'Italian',4 => 'Dutch',5 => 'Swedish',6 => 'Spanish',7 => 'Danish',8 => 'Portuguese',9 => 'Norwegian',10 => 'Hebrew',11 => 'Japanese',12 => 'Arabic',13 => 'Finnish',14 => 'Greek',15 => 'Icelandic',16 => 'Maltese',17 => 'Turkish',18 => 'Croatian',19 => 'Chinese (Traditional)',20 => 'Urdu',21 => 'Hindi',22 => 'Thai',23 => 'Korean',24 => 'Lithuanian',25 => 'Polish',26 => 'Hungarian',27 => 'Estonian',28 => 'Latvian',29 => 'Sami',30 => 'Faroese',31 => 'Farsi/Persian',32 => 'Russian',33 => 'Chinese (Simplified)',34 => 'Flemish',35 => 'Irish Gaelic',36 => 'Albanian',37 => 'Romanian',38 => 'Czech',39 => 'Slovak',40 => 'Slovenian',41 => 'Yiddish',42 => 'Serbian',43 => 'Macedonian',44 => 'Bulgarian',45 => 'Ukrainian',46 => 'Byelorussian',47 => 'Uzbek',48 => 'Kazakh',49 => 'Azerbaijani (Cyrillic script)',50 => 'Azerbaijani (Arabic script)',51 => 'Armenian',52 => 'Georgian',53 => 'Moldavian',54 => 'Kirghiz',55 => 'Tajiki',56 => 'Turkmen',57 => 'Mongolian (Mongolian script)',58 => 'Mongolian (Cyrillic script)',59 => 'Pashto',60 => 'Kurdish',61 => 'Kashmiri',62 => 'Sindhi',63 => 'Tibetan',64 => 'Nepali',65 => 'Sanskrit',66 => 'Marathi',67 => 'Bengali',68 => 'Assamese',69 => 'Gujarati',70 => 'Punjabi',71 => 'Oriya',72 => 'Malayalam',73 => 'Kannada',74 => 'Tamil',75 => 'Telugu',76 => 'Sinhalese',77 => 'Burmese',78 => 'Khmer',79 => 'Lao',80 => 'Vietnamese',81 => 'Indonesian',82 => 'Tagalong',83 => 'Malay (Roman script)',84 => 'Malay (Arabic script)',85 => 'Amharic',86 => 'Tigrinya',87 => 'Galla',88 => 'Somali',89 => 'Swahili',90 => 'Kinyarwanda/Ruanda',91 => 'Rundi',92 => 'Nyanja/Chewa',93 => 'Malagasy',94 => 'Esperanto',128 => 'Welsh',129 => 'Basque',130 => 'Catalan',131 => 'Latin',132 => 'Quenchua',133 => 'Guarani',134 => 'Aymara',135 => 'Tatar',136 => 'Uighur',137 => 'Dzongkha',138 => 'Javanese (Roman script)',139 => 'Sundanese (Roman script)',140 => 'Galician',141 => 'Afrikaans',142 => 'Breton',14 => 'Inuktitut',144 => 'Scottish Gaelic',145 => 'Manx Gaelic',146 => 'Irish Gaelic (with dot above)',147 => 'Tongan',148 => 'Greek (polytonic)',149 => 'Greenlandic',150 => 'Azerbaijani (Roman script)');
		if(array_key_exists($id,$language)) {
			return $language[$id];
		} else {
			return 'undocumented languageID ('.$id.')';
		}
	}

	function txt_languageID_MS($id) {
		/* http://www.microsoft.com/globaldev/reference/lcid-all.mspx */
		$language = array(1078 => 'Afrikaans - South Africa',1052 => 'Albanian - Albania',1118 => 'Amharic - Ethiopia',1025 => 'Arabic - Saudi Arabia',5121 => 'Arabic - Algeria',15361 => 'Arabic - Bahrain',3073 => 'Arabic - Egypt',2049 => 'Arabic - Iraq',11265 => 'Arabic - Jordan',13313 => 'Arabic - Kuwait',12289 => 'Arabic - Lebanon',4097 => 'Arabic - Libya',6145 => 'Arabic - Morocco',8193 => 'Arabic - Oman',16385 => 'Arabic - Qatar',10241 => 'Arabic - Syria',7169 => 'Arabic - Tunisia',14337 => 'Arabic - U.A.E.',9217 => 'Arabic - Yemen',1067 => 'Armenian - Armenia',1101 => 'Assamese',2092 => 'Azeri (Cyrillic)',1068 => 'Azeri (Latin)',1069 => 'Basque',1059 => 'Belarusian',1093 => 'Bengali (India)',2117 => 'Bengali (Bangladesh)',5146 => 'Bosnian (Bosnia/Herzegovina)',1026 => 'Bulgarian',1109 => 'Burmese',1027 => 'Catalan',1116 => 'Cherokee - United States',2052 => 'Chinese - People\'s Republic of China',4100 => 'Chinese - Singapore',1028 => 'Chinese - Taiwan',3076 => 'Chinese - Hong Kong SAR',5124 => 'Chinese - Macao SAR',1050 => 'Croatian',4122 => 'Croatian (Bosnia/Herzegovina)',1029 => 'Czech',1030 => 'Danish',1125 => 'Divehi',1043 => 'Dutch - Netherlands',2067 => 'Dutch - Belgium',1126 => 'Edo',1033 => 'English - United States',2057 => 'English - United Kingdom',3081 => 'English - Australia',10249 => 'English - Belize',4105 => 'English - Canada',9225 => 'English - Caribbean',15369 => 'English - Hong Kong SAR',16393 => 'English - India',14345 => 'English - Indonesia',6153 => 'English - Ireland',8201 => 'English - Jamaica',17417 => 'English - Malaysia',5129 => 'English - New Zealand',13321 => 'English - Philippines',18441 => 'English - Singapore',7177 => 'English - South Africa',11273 => 'English - Trinidad',12297 => 'English - Zimbabwe',1061 => 'Estonian',1080 => 'Faroese',1065 => 'Farsi',1124 => 'Filipino',1035 => 'Finnish',1036 => 'French - France',2060 => 'French - Belgium',11276 => 'French - Cameroon',3084 => 'French - Canada',9228 => 'French - Democratic Rep. of Congo',12300 => 'French - Cote d\'Ivoire',15372 => 'French - Haiti',5132 => 'French - Luxembourg',13324 => 'French - Mali',6156 => 'French - Monaco',14348 => 'French - Morocco',58380 => 'French - North Africa',8204 => 'French - Reunion',10252 => 'French - Senegal',4108 => 'French - Switzerland',7180 => 'French - West Indies',1122 => 'Frisian - Netherlands',1127 => 'Fulfulde - Nigeria',1071 => 'FYRO Macedonian',2108 => 'Gaelic (Ireland)',1084 => 'Gaelic (Scotland)',1110 => 'Galician',1079 => 'Georgian',1031 => 'German - Germany',3079 => 'German - Austria',5127 => 'German - Liechtenstein',4103 => 'German - Luxembourg',2055 => 'German - Switzerland',1032 => 'Greek',1140 => 'Guarani - Paraguay',1095 => 'Gujarati',1128 => 'Hausa - Nigeria',1141 => 'Hawaiian - United States',1037 => 'Hebrew',1081 => 'Hindi',1038 => 'Hungarian',1129 => 'Ibibio - Nigeria',1039 => 'Icelandic',1136 => 'Igbo - Nigeria',1057 => 'Indonesian',1117 => 'Inuktitut',1040 => 'Italian - Italy',2064 => 'Italian - Switzerland',1041 => 'Japanese',1099 => 'Kannada',1137 => 'Kanuri - Nigeria',2144 => 'Kashmiri',1120 => 'Kashmiri (Arabic)',1087 => 'Kazakh',1107 => 'Khmer',1111 => 'Konkani',1042 => 'Korean',1088 => 'Kyrgyz (Cyrillic)',1108 => 'Lao',1142 => 'Latin',1062 => 'Latvian',1063 => 'Lithuanian',1086 => 'Malay - Malaysia',2110 => 'Malay - Brunei Darussalam',1100 => 'Malayalam',1082 => 'Maltese',1112 => 'Manipuri',1153 => 'Maori - New Zealand',1102 => 'Marathi',1104 => 'Mongolian (Cyrillic)',2128 => 'Mongolian (Mongolian)',1121 => 'Nepali',2145 => 'Nepali - India',1044 => 'Norwegian (Bokmål)',2068 => 'Norwegian (Nynorsk)',1096 => 'Oriya',1138 => 'Oromo',1145 => 'Papiamentu',1123 => 'Pashto',1045 => 'Polish',1046 => 'Portuguese - Brazil',2070 => 'Portuguese - Portugal',1094 => 'Punjabi',2118 => 'Punjabi (Pakistan)',1131 => 'Quecha - Bolivia',2155 => 'Quecha - Ecuador',3179 => 'Quecha - Peru',1047 => 'Rhaeto-Romanic',1048 => 'Romanian',2072 => 'Romanian - Moldava',1049 => 'Russian',2073 => 'Russian - Moldava',1083 => 'Sami (Lappish)',1103 => 'Sanskrit',1132 => 'Sepedi',3098 => 'Serbian (Cyrillic)',2074 => 'Serbian (Latin)',1113 => 'Sindhi - India',2137 => 'Sindhi - Pakistan',1115 => 'Sinhalese - Sri Lanka',1051 => 'Slovak',1060 => 'Slovenian',1143 => 'Somali',1070 => 'Sorbian',3082 => 'Spanish - Spain (Modern Sort)',1034 => 'Spanish - Spain (Traditional Sort)',11274 => 'Spanish - Argentina',16394 => 'Spanish - Bolivia',13322 => 'Spanish - Chile',9226 => 'Spanish - Colombia',5130 => 'Spanish - Costa Rica',7178 => 'Spanish - Dominican Republic',12298 => 'Spanish - Ecuador',17418 => 'Spanish - El Salvador',4106 => 'Spanish - Guatemala',18442 => 'Spanish - Honduras',58378 => 'Spanish - Latin America',2058 => 'Spanish - Mexico',19466 => 'Spanish - Nicaragua',6154 => 'Spanish - Panama',15370 => 'Spanish - Paraguay',10250 => 'Spanish - Peru',20490 => 'Spanish - Puerto Rico',21514 => 'Spanish - United States',14346 => 'Spanish - Uruguay',8202 => 'Spanish - Venezuela',1072 => 'Sutu',1089 => 'Swahili',1053 => 'Swedish',2077 => 'Swedish - Finland',1114 => 'Syriac',1064 => 'Tajik',1119 => 'Tamazight (Arabic)',2143 => 'Tamazight (Latin)',1097 => 'Tamil',1092 => 'Tatar',1098 => 'Telugu',1054 => 'Thai',2129 => 'Tibetan - Bhutan',1105 => 'Tibetan - People\'s Republic of China',2163 => 'Tigrigna - Eritrea',1139 => 'Tigrigna - Ethiopia',1073 => 'Tsonga',1074 => 'Tswana',1055 => 'Turkish',1090 => 'Turkmen',1152 => 'Uighur - China',1058 => 'Ukrainian',1056 => 'Urdu',2080 => 'Urdu - India',2115 => 'Uzbek (Cyrillic)',1091 => 'Uzbek (Latin)',1075 => 'Venda',1066 => 'Vietnamese',1106 => 'Welsh',1076 => 'Xhosa',1144 => 'Yi',1085 => 'Yiddish',1130 => 'Yoruba',1077 => 'Zulu',1279 => 'HID (Human Interface Device)');
		if(array_key_exists($id,$language)) {
			return $language[$id];
		} else {
			return 'undocumented languageID ('.$id.')';
		}
	}

	function txt_nameID($id) {
		$name = array(0 => 'Copyright notice',1 => 'Font Family name',2 => 'Font Subfamily name',3 => 'Unique font identifier',4 => 'Full font name',5 => 'Version string',6 => 'Postscript name',7 => 'Trademark',8 => 'Manufacturer Name',9 => 'Designer',10 => 'Description',11 => 'URL Vendor',12  => 'URL Designer',13  => 'License Description',14  => 'License Info URL',15  => 'Reserved',16  => 'Preferred Family',17  => 'Preferred Subfamily',18  => 'Compatible Full',19  => 'Sample text',20  => 'PostScript CID findfont name');
		if(array_key_exists($id,$name)) {
			return $name[$id];
		} else {
			return 'undocumented nameID ('.$id.')';
		}
	}

	function txt_achVendID($id) {
		/* http://www.microsoft.com/typography/links/VendorList.aspx */
		$vendors = array('!etf' => '!Exclamachine Type Foundry','1asc' => 'Ascender Corporation','1bou' => 'Boutros International','2reb' => '2Rebels','39bc' => 'Finley\'s Barcode Fonts','3ip' => 'Three Islands Press','5pts' => 'Five Points Technology','918' => 'RavenType','abbo' => 'Arabic Dictionary Lab','abc' => 'Altek Instruments','abou' => 'Aboutype, Inc.','acut' => 'Acute Type','adbe' => 'Adobe','aef' => 'Altered Ego Fonts','agfa' => 'Monotype Imaging (replaced by MONO)','alfa' => 'Alphabets*Inc','alph' => 'Alphameric Broadcast Solutions Limited','alpn' => 'Alpona Portal','alte' => 'Altemus','alts' => 'Altsys / Made with Fontographer','ando' => 'Osam Ando','anty' => 'Anatoletype','aop' => 'an Art Of Pengwyn','aply' => 'Apply Interactive','apos' => 'Apostrophic Laboratories','appl' => 'Apple','arch' => 'Architext','arph' => 'Arphic Technology Co.','ars' => 'EN ARS Ltd.','arty' => 'Archive Type','assa' => 'astype','asym' => 'Applied Symbols','atec' => 'Page Technology Marketing, Inc.','atf1' => 'Australian Type Foundry','auto' => 'Autodidakt','azls' => 'Azalea Software, Inc.','b&h' => 'Bigelow & Holmes','bars' => 'CIA (BAR CODES) UK','base' => 'Baseline Fonts','bcp' => 'Barcode Products Ltd','bert' => 'Berthold','bitm' => 'Bitmap Software','bits' => 'Bitstream','bizf' => 'Bizfonts.com','blab' => 'BaseLab','blah' => 'Mister Bla\'s Fontworx','bli' => 'Blissym Language Institute','borw' => 'em2 Solutions','boyb' => 'BoyBeaver Fonts','brem' => 'Mark Bremmer','brtc' => 'ITSCO - Bar Code Fonts','bs' => 'Barcodesoft','bwfw' => 'B/W Fontworks','c&c' => 'Carter & Cone','c21' => 'Club 21','cak' => 'pluginfonts.com','cano' => 'Canon','casl' => 'H.W. Caslon & Company Ltd.','cb' => 'Christian Büning','cdac' => 'Centre for Development of Advanced Computing','cfa' => 'Computer Fonts Australia','char' => 'Characters','cktp' => 'CakeType','conr' => 'Connare.com','cool' => 'Cool Fonts','cord' => 'corduroy','ct' => 'CastleType','ctdl' => 'China Type Designs Ltd.','cwwf' => 'Computers World Wide/AC Capital Funding','cype' => 'Club Type','dama' => 'Dalton Maag Limited','dd' => 'Devon DeLapp','deco' => 'DecoType (replaced by DT)','delv' => 'Delve Fonts','dfs' => 'Datascan Font Service Ltd','dgl' => 'Digital Graphic Labs foundry','ds' => 'Dainippon Screen Mfg. Co., Inc.','dsbv' => 'Datascan bv','dsci' => 'Design Science Inc.','dsst' => 'Dubina Nikolay','dt' => 'DecoType','dtc' => 'Digital Typeface Corp.','dtps' => 'DTP-Software','dtpt' => 'dtpTypes Limited','duxb' => 'Duxbury Systems, Inc.','dyna' => 'DynaLab','edge' => 'Rivers Edge Corp.','ef' => 'Elsner+Flake','eff' => 'Electronic Font Foundry','efi' => 'Elfring Fonts Inc.','efnt' => 'E Fonts L.L.C.','else' => 'Elseware','emgr' => 'Emigre','epsn' => 'Epson','esig' => 'E-Signature','ever' => 'Evertype','fbi' => 'The Font Bureau, Inc.','fcab' => 'The Font Cabinet','fcan' => 'fontage canada','fdi' => 'FDI fonts.info','fjty' => 'Frank Jonen - Illustration & Typography','fmfo' => 'Studio Liddell Graphic Design','fntf' => 'Fontfoundry','fofa' => 'FontFabrik','font' => 'Font Source','foun' => 'The Foundry','frml' => 'formlos','fs' => 'Formula Solutions','fse' => 'Font Source Europe','fsi' => 'FontShop International','fsl' => 'FontSurfer Ltd','fsmi' => 'Fontsmith','ftft' => 'FontFont','ftgd' => 'Font Garden','ftn' => 'Fountain','fwre' => 'Fontware Limited','gala' => 'Galápagos Design Group, Inc.','galo' => 'Gerald Gallo','gari' => 'Gary Ritchie','gd' => 'GD Fonts','gf' => 'GarageFonts','gia' => 'Georgian Internet Avenue','glyf' => 'Glyph Systems','goat' => 'Dingbat Dungeon','gogo' => 'Fonts-A-Go-Go','gpi' => 'Gamma Productions, Inc.','gril' => 'Grilled cheese','grro' => 'grafikk RØren','gt' => 'Graphity!','h&fj' => 'Hoefler & Frere-Jones','haus' => 'TypeHaus','heb' => 'Sivan Toledo','hfj' => 'Hoefler & Frere-Jones (replaced by H&FJ)','hill' => 'Hill Systems','hl' => 'High-Logic','hous' => 'House Industries','hp' => 'Hewlett-Packard','hs' => 'HermesSOFT Company','htf' => 'The Hoefler Type Foundry, Inc.','hxtp' => 'Hexatype','hy' => 'HanYang System','ibm' => 'IBM','idau' => 'IDAutomation.com, Inc.','idee' => 'IDEE TYPOGRAFICA','idf' => 'International Digital Fonts','ilp' => 'Indigenous Languages Project','impr' => 'Impress','inra' => 'INRAY Inc.','invc' => 'Invoice Central','invd' => 'TYPE INVADERS','ise' => 'ISE-Aditi Info. Pvt . Ltd.','itc' => 'ITC','itf' => 'Red Rooster Collection (ITF, Inc.)','jaf' => 'Just Another Foundry','jptt' => 'Jeremy Tankard Typography','jy' => 'JIYUKOBO Ltd.','katf' => 'Kingsley/ATF','kf' => 'Karakta Fonthome','klim' => 'Klim Typographic Design','kltf' => 'Karsten Luecke','kork' => 'Khork OÜ','kuba' => 'Kuba Tatarkiewicz','lait' => 'la laiterie','lans' => 'Lanston Type Company','lara' => 'Larabiefonts','laud' => 'Carolina Laudon','leaf' => 'Interleaf, Inc.','letr' => 'Letraset','lgx' => 'Logix Research Institute, Inc.','lhf' => 'Letterhead Fonts','ling' => 'Linguist\'s Software','lino' => 'Linotype','live' => 'Livedesign','lp' => 'LetterPerfect Fonts','lt' => 'Le Typophage','ltrx' => 'Lighttracks','lttr' => 'LettError','lud' => 'Ludlow','lufo' => 'LucasFonts','macr' => 'Macromedia / Made with Fontographer','madt' => 'MADType','maps' => 'Tom Mouat\'s Map Symbol Fonts','mats' => 'Match Fonts','mc' => 'Cerajewski Computer Consulting','mcow' => 'Mountaincow','meir' => 'Meir Sadan','mesa' => 'FontMesa,','mf' => 'Magic Fonts','mfnt' => 'Masterfont','mill' => 'Millan','mj' => 'Majus Corporation','mjr' => 'Majur Inc.','mlgc' => 'Micrologic Software','mmft' => 'Michel M.','modi' => 'Modular Infotech Private Limited.','monb' => 'Monib','mone' => 'Meta One','mono' => 'Monotype Imaging','moon' => 'Moonlight Type and Technolog','mrv' => 'Morovia Corporation','ms' => 'Microsoft Corporation','mscr' => 'Majus Corporation','mse' => 'MSE-iT','msft' => 'Microsoft Corporation','mt' => 'Monotype Imaging (replaced by MONO)','mty' => 'Motoya Co. ,LTD.','mutf' => 'Murasu Systems Sdn. Bhd','myfo' => 'MyFonts.com','nb' => 'No Bodoni Typography','ncnd' => '&cond','ndtc' => 'Neufville Digital','nec' => 'NEC Corporation','nick' => 'Nick\'s Fonts','nis' => 'NIS Corporation','norf' => 'Norfok Incredible Font Design','nova' => 'NOVATYPE','orbi' => 'Orbit Enterprises, Inc.','ourt' => 'Ourtype','p22' => 'P22 Inc.','para' => 'ParaType Inc.','pdwx' => 'Parsons Design Workx','pf' => 'Phil\'s Fonts, Inc.','pkdd' => 'Philip Kelly Digital Design','plat' => 'PLATINUM technology','prfs' => 'Production First Software','psy' => 'PSY/OPS','ptf' => 'Porchez Typofonderie','ptmi' => 'Page Technology Marketing, Inc.','ptyp' => 'preussTYPE','pyrs' => 'PYRS Fontlab Ltd. / Made with FontLab','qmsi' => 'QMS/Imagen','qrat' => 'Quadrat Communications','real' => 'Underware','rjps' => 'Reall Graphics','rkfn' => 'R K Fonts','robo' => 'Buro Petr van Blokland','rrt' => 'Red Rooster Collection (ITF, Inc.)','rudy' => 'RudynFluffy','ryob' => 'Ryobi Limited','sand' => 'Sandoll','sax' => 's.a.x. Software gmbh','sbt' => 'SelfBuild Type Foundry','sean' => 'The FontSite','sfs' => 'Sarumadhu Services Pvt. Ltd.','sfun' => 'Software Union','sg' => 'Scooter Graphics','sham' => 'ShamFonts / Shamrock Int.','shft' => 'Shift','sig' => 'Signature Software, Inc.','sil' => 'SIL International','sit' => 'Summit Information Technologies Pvt.Ltd,','skz' => 'Celtic Lady\'s Fonts','sl' => 'Silesian Letters','sn' => 'SourceNet','soho' => 'Soft Horizons','sos' => 'Standing Ovations Software','stf' => 'Brian Sooy & Co + Sooy Type Foundry','stor' => 'Storm Type Foundry','styp' => 'Stone Type Foundry','sunw' => 'sunwalk fontworks','swft' => 'Swfte International','syn' => 'SynFonts','syrc' => 'Syriac Computing Institute','tc' => 'Typeco','tdr' => 'Tansin A. Darcos & Co.','term' => 'Terminal Design, Inc.','tf' => 'Treacyfaces / Headliners','tild' => 'Tilde, SIA','timo' => 'Tim Romano','timr' => 'Tim Rolands','tiro' => 'Tiro Typeworks','tmf' => 'The MicroFoundry','tptc' => 'Test Pilot Collective','tptq' => 'Typotheque','tr' => 'Type Revivals','ts' => 'TamilSoft Corporation','ttg' => 'Twardoch Typography','tycu' => 'TypeCulture','typa' => 'Typadelic','type' => 'Type Associates Pty Ltd','typo' => 'Typodermic','typr' => 'Type Project','tyre' => 'typerepublic','ua' => 'UnAuthorized Type','undt' => 'ÜNDT','urw' => 'URW++','ut' => 'Unitype Inc','vkp' => 'Vijay K. Patel','vlkf' => 'Visualogik Technology & Design','vog' => 'Martin Vogel','vrom' => 'Vladimir Romanov','vs' => 'VorSicht GmbH','vt' => 'VISUALTYPE SRL','wasp' => 'Wasp barcode','wm' => 'Webmakers India','xfc' => 'Xerox Font Services','y&y' => 'Y&Y, Inc.','zane' => 'Unrender','zegr' => 'Zebra Font Factory','zeta' => 'Tangram Studio','zsft' => 'Zsoft','pfed' => 'Created with FontForge','sino' => 'Changzhou SinoType Technology Co., Ltd.','wine' => 'Wine','xtt' => 'XenoType Technologies');
		if(array_key_exists(strtolower(trim($id)),$vendors)) {
			return $vendors[strtolower(trim($id))];
		} else {
			return 'unknown Vendor ID ('.$id.')';
		}
	}

	function txt_os2_weight($id) {
		$weight = array(100 => 'Thin',200 => 'Extra-light (Ultra-light)',300 => 'Light',400 => 'Normal (Regular)',500 => 'Medium',600 => 'Semi-bold (Demi-bold)',700 => 'Bold',800 => 'Extra-bold (Ultra-bold)',900 => 'Black (Heavy)',1 => 'Ultra-light',2 => 'Extra-light',3 => 'Light',4 => 'Semi-light',5 => 'Medium (normal)',6 => 'Semi-bold',7 => 'Bold',8 => 'Extra-Bold',9 => 'Ultra-bold');
		if(array_key_exists($id,$weight)) {
			return $weight[$id];
		} else {
			return 'unknown weight ('.$id.')';
		}
	}

	function txt_os2_width($id) {
		$width = array(1 => 'Ultra-condensed',2 => 'Extra-condensed',3 => 'Condensed',4 => 'Semi-condensed',5 => 'Medium (normal)',6 => 'Semi-expanded',7 => 'Expanded',8 => 'Extra-expanded',9 => 'Ultra-expanded');
		if(array_key_exists($id,$width)) {
			return $width[$id];
		} else {
			return 'unknown width ('.$id.')';
		}

	}

	function txt_os2_sFamilyClass($class, $subclass) {
		// http://www.microsoft.com/typography/otspec/ibmfc.htm
		switch($class) {
			case 0:
				$class_str = 'No Classification';
				break;
			case 1:
				$class_str = 'Oldstyle Serifs';
				switch($subclass) {
					case 0:
						$subclass_str = 'No Classification';
						break;
					case 1:
						$subclass_str = 'IBM Rounded Legibility';
						break;
					case 2:
						$subclass_str = 'Garalde';
						break;
					case 3:
						$subclass_str = 'Venetian';
						break;
					case 4:
						$subclass_str = 'Modified Venetian';
						break;
					case 5:
						$subclass_str = 'Dutch Modern';
						break;
					case 6:
						$subclass_str = 'Dutch Traditional';
						break;
					case 7:
						$subclass_str = 'Contemporary';
						break;
					case 8:
						$subclass_str = 'Calligraphic';
						break;
					case 15:
						$subclass_str = 'Miscellaneous';
						break;
					default:
						$subclass_str = 'unknown subclass ('.$subclass.')';
						break;
				}
				break;
			case 2:
				$class_str = 'Transitional Serifs';
				switch($subclass) {
					case 0:
						$subclass_str = 'No Classification';
						break;
					case 1:
						$subclass_str = 'Direct Line';
						break;
					case 2:
						$subclass_str = 'Script';
						break;
					case 15:
						$subclass_str = 'Miscellaneous';
						break;
					default:
						$subclass_str = 'unknown subclass ('.$subclass.')';
						break;
				}
				break;
			case 3:
				$class_str = 'Modern Serifs';
				switch($subclass) {
					case 0:
						$subclass_str = 'No Classification';
						break;
					case 1:
						$subclass_str = 'Italian';
						break;
					case 2:
						$subclass_str = 'Script';
						break;
					case 15:
						$subclass_str = 'Miscellaneous';
						break;
					default:
						$subclass_str = 'unknown subclass ('.$subclass.')';
						break;
				}
				break;
			case 4:
				$class_str = 'Clarendon Serifs';
				switch($subclass) {
					case 0:
						$subclass_str = 'No Classification';
						break;
					case 1:
						$subclass_str = 'Clarendon';
						break;
					case 2:
						$subclass_str = 'Modern';
						break;
					case 3:
						$subclass_str = 'Traditional';
						break;
					case 4:
						$subclass_str = 'Newspaper';
						break;
					case 5:
						$subclass_str = 'Stub Serif';
						break;
					case 6:
						$subclass_str = 'Monotone';
						break;
					case 7:
						$subclass_str = 'Typewriter';
						break;
					case 15:
						$subclass_str = 'Miscellaneous';
						break;
					default:
						$subclass_str = 'unknown subclass ('.$subclass.')';
						break;
				}
				break;
			case 5:
				$class_str = 'Slab Serifs';
				switch($subclass) {
					case 0:
						$subclass_str = 'No Classification';
						break;
					case 1:
						$subclass_str = 'Monotone';
						break;
					case 2:
						$subclass_str = 'Humanist';
						break;
					case 3:
						$subclass_str = 'Geometric';
						break;
					case 4:
						$subclass_str = 'Swiss';
						break;
					case 5:
						$subclass_str = 'Typewriter';
						break;
					case 15:
						$subclass_str = 'Miscellaneous';
						break;
					default:
						$subclass_str = 'unknown subclass ('.$subclass.')';
						break;
				}
				break;
			case 7:
				$class_str = 'Freeform Serifs';
				switch($subclass) {
					case 0:
						$subclass_str = 'No Classification';
						break;
					case 1:
						$subclass_str = 'Modern';
						break;
					case 15:
						$subclass_str = 'Miscellaneous';
						break;
					default:
						$subclass_str = 'unknown subclass ('.$subclass.')';
						break;
				}
				break;
			case 8:
				$class_str = 'Sans Serif';
				switch($subclass) {
					case 0:
						$subclass_str = 'No Classification';
						break;
					case 1:
						$subclass_str = 'IBM Neo-grotesque Gothic';
						break;
					case 2:
						$subclass_str = 'Humanist';
						break;
					case 3:
						$subclass_str = 'Low-x Round Geometric';
						break;
					case 4:
						$subclass_str = 'High-x Round Geometric';
						break;
					case 5:
						$subclass_str = 'Neo-grotesque Gothic';
						break;
					case 6:
						$subclass_str = 'Modified Neo-grotesque Gothic';
						break;
					case 9:
						$subclass_str = 'Typewriter Gothic';
						break;
					case 10:
						$subclass_str = 'Matrix';
						break;
					case 15:
						$subclass_str = 'Miscellaneous';
						break;
					default:
						$subclass_str = 'unknown subclass ('.$subclass.')';
						break;
				}
				break;
			case 9:
				$class_str = 'Ornamentals';
				switch($subclass) {
					case 0:
						$subclass_str = 'No Classification';
						break;
					case 1:
						$subclass_str = 'Engraver';
						break;
					case 2:
						$subclass_str = 'Black Letter';
						break;
					case 3:
						$subclass_str = 'Decorative';
						break;
					case 4:
						$subclass_str = 'Three Dimensional';
						break;
					case 15:
						$subclass_str = 'Miscellaneous';
						break;
					default:
						$subclass_str = 'unknown subclass ('.$subclass.')';
						break;
				}
				break;
			case 10:
				$class_str = 'Scripts';
				switch($subclass) {
					case 0:
						$subclass_str = 'No Classification';
						break;
					case 1:
						$subclass_str = 'Uncial';
						break;
					case 2:
						$subclass_str = 'Brush Joined';
						break;
					case 3:
						$subclass_str = 'Formal Joined';
						break;
					case 4:
						$subclass_str = 'Monotone Joined';
						break;
					case 5:
						$subclass_str = 'Calligraphic';
						break;
					case 6:
						$subclass_str = 'Brush Unjoined';
						break;
					case 7:
						$subclass_str = 'Formal Unjoined';
						break;
					case 8:
						$subclass_str = 'Monotone Unjoined';
						break;
					case 15:
						$subclass_str = 'Miscellaneous';
						break;
					default:
						$subclass_str = 'unknown subclass ('.$subclass.')';
						break;
				}
				break;
			case 12:
				$class_str = 'Symbolic';
				switch($subclass) {
					case 0:
						$subclass_str = 'No Classification';
						break;
					case 3:
						$subclass_str = 'Mixed Serif';
						break;
					case 6:
						$subclass_str = 'Oldstyle Serif';
						break;
					case 7:
						$subclass_str = 'Neo-grotesque Sans Serif';
						break;
					case 15:
						$subclass_str = 'Miscellaneous';
						break;
					default:
						$subclass_str = 'unknown subclass ('.$subclass.')';
						break;
				}
				break;
			default:
				$class_str = 'unknown class ('.$class.')';
				break;
		}
		return array($class_str, $subclass_str);
	}

	function txt_os2_panose($FamilyType, $FamilySubType=null, $field=null) {
		if($field === null) {
			$FamilyType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Latin Text',3 => 'Latin Hand Written',4 => 'Latin Decorative',5 => 'Latin Symbol');
			if(array_key_exists($FamilyType,$FamilyType_str)) {
				return $FamilyType_str[$FamilyType];
			} else {
				return 'unknown family type ('.$FamilyType.')';
			}
		} else {
			switch($field) {
				case 1:
					switch($FamilyType) {
						case 2:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Cove',3 => 'Obtuse Cove',4 => 'Square Cove',5 => 'Obtuse Square Cove',6 => 'Square',7 => 'Thin',8 => 'Oval',9 => 'Exaggerated',10 => 'Triangle',11 => 'Normal Sans',12 => 'Obtuse Sans',13 => 'Perpendicular Sans',14 => 'Flared',15 => 'Rounded');
							break;
						case 3:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Flat Nib',3 => 'Pressure Point',4 => 'Engraved',5 => 'Ball (Round Cap)',6 => 'Brush',7 => 'Rough',8 => 'Felt Pen/Brush Tip',9 => 'Wild Brush - Drips a lot');
							break;
						case 4:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Derivative',3 => 'Non-standard Topology',4 => 'Non-standard Elements',5 => ' Non-standard Aspect',6 => 'Initials',7 => 'Cartoon',8 => 'Picture Stems',9 => 'Ornamented',10 => 'Text and Background',11 => 'Collage',12 => 'Montage');
							break;
						case 5:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Montages',3 => 'Pictures',4 => 'Shapes',5 => 'Scientific',6 => 'Music',7 => 'Expert',8 => 'Patterns',9 => 'Boarders',10 => 'Icons',11 => 'Logos',12 => 'Industry specific');
							break;
					}
					break;
				case 2:
					switch($FamilyType) {
						case 2:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Very Light',3 => 'Light',4 => 'Thin',5 => 'Book',6 => 'Medium',7 => 'Demi',8 => 'Bold',9 => 'Heavy',10 => 'Black',11 => 'Extra Black');
							break;
						case 3:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Very Light',3 => 'Light',4 => 'Thin',5 => 'Book',6 => 'Medium',7 => 'Demi',8 => 'Bold',9 => 'Heavy',10 => 'Black',11 => 'Extra Black (Nord)');
							break;
						case 4:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Very Light',3 => 'Light',4 => 'Thin',5 => 'Book',6 => 'Medium',7 => 'Demi',8 => 'Bold',9 => 'Heavy',10 => 'Black',11 => 'Extra Black');
							break;
						case 5:
							$FamilySubType_str = array(1 => 'No Fit');
							break;
					}
					break;
				case 3:
					switch($FamilyType) {
						case 2:
							$FamilySubType_str = array(0 => 'Any',1 => 'No fit',2 => 'Old Style',3 => 'Modern',4 => 'Even Width',5 => 'Extended',6 => 'Condensed',7 => 'Very Extended',8 => 'Very Condensed',9 => 'Monospaced');
							break;
						case 3:
							$FamilySubType_str = array(0 => 'Any',1 => 'No fit',2 => 'Proportional Spaced',3 => 'Monospaced');
							break;
						case 4:
							$FamilySubType_str = array(0 => 'Any',1 => 'No fit',2 => 'Super Condensed',3 => 'Very Condensed',4 => 'Condensed',5 => 'Normal',6 => 'Extended',7 => 'Very Extended',8 => 'Super Extended',9 => 'Monospaced');
							break;
						case 5:
							$FamilySubType_str = array(0 => 'Any',1 => 'No fit',2 => 'Proportional Spaced',3 => 'Monospaced');
							break;
					}
					break;
				case 4:
					switch($FamilyType) {
						case 2:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'None',3 => 'Very Low',4 => 'Low',5 => 'Medium Low',6 => 'Medium',7 => 'Medium High',8 => 'High',9 => 'Very High');
							break;
						case 3:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Very Condensed',3 => 'Condensed',4 => 'Normal',5 => 'Expanded',6 => 'Very Expanded');
							break;
						case 4:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'None',3 => 'Very Low',4 => 'Low',5 => 'Medium Low',6 => 'Medium',7 => 'Medium High',8 => 'High',9 => 'Very High',10 => 'Horizontal Low',11 => 'Horizontal Medium',12 => 'Horizontal High',13 => 'Broken');
							break;
						case 5:
							$FamilySubType_str = array(1 => 'No Fit');
							break;
					}
					break;
				case 5:
					switch($FamilyType) {
						case 2:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'No Variation',3 => 'Gradual/Diagonal',4 => 'Gradual/Transitional',5 => 'Gradual/Vertical',6 => 'Gradual/Horizontal',7 => 'Rapid/Vertical',8 => 'Rapid/Horizontal',9 => 'Instant/Vertical',10 => 'Instant/Horizontal');
							break;
						case 3:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'None',3 => 'Very Low',4 => 'Low',5 => 'Medium Low',6 => 'Medium',7 => 'Medium High',8 => 'High',9 => 'Very High');
							break;
						case 4:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Cove',3 => 'Obtuse Cove',4 => 'Square Cove',5 => 'Obtuse Square Cove',6 => 'Square',7 => 'Thin',8 => 'Oval',9 => 'Exaggerated',10 => 'Triangle',11 => 'Normal Sans',12 => 'Obtuse Sans',13 => 'Perpendicular Sans',14 => 'Flared',15 => 'Rounded',16 => 'Script');
							break;
						case 5:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'No Width',3 => 'Exceptionally Wide',4 => 'Super Wide',5 => 'Very Wide',6 => 'Wide',7 => 'Normal',8 => 'Narrow',9 => 'Very Narrow');
							break;
					}
					break;
				case 6:
					switch($FamilyType) {
						case 2:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Straight Arms/Horizontal',3 => 'Straight Arms/Wedge',4 => 'Straight Arms/Vertical',5 => 'Straight Arms/Single Serif',6 => 'Straight Arms/Double Serif',7 => 'Non-Straight/Horizontal',8 => 'Non-Straight/Wedge',9 => 'Non-Straight/Vertical',10 => 'Non-Straight/Single Serif',11 => 'Non-Straight/Double Serif');
							break;
						case 3:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Roman Disconnected',3 => 'Roman Trailing',4 => 'Roman Connected',5 => 'Cursive Disconnected',6 => 'Cursive Trailing',7 => 'Cursive Connected',8 => 'Blackletter Disconnected',9 => 'Blackletter Trailing',10 => 'Blackletter Connected');
							break;
						case 4:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'None - Standard Solid Fill',3 => 'White / No Fill',4 => 'Patterned Fill',5 => 'Complex Fill',6 => 'Shaped Fill',7 => 'Drawn / Distressed');
							break;
						case 5:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'No Width',3 => 'Exceptionally Wide',4 => 'Super Wide',5 => 'Very Wide',6 => 'Wide',7 => 'Normal',8 => 'Narrow',9 => 'Very Narrow');
							break;
					}
					break;
				case 7:
					switch($FamilyType) {
						case 2:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Normal/Contact',3 => 'Normal/Weighted',4 => 'Normal/Boxed',5 => 'Normal/Flattened',6 => 'Normal/Rounded',7 => 'Normal/Off Center',8 => 'Normal/Square',9 => 'Oblique/Contact',10 => 'Oblique/Weighted',11 => 'Oblique/Boxed',12 => 'Oblique/Flattened',13 => 'Oblique/Rounded',14 => 'Oblique/Off Center',15 => 'Oblique/Square');
							break;
						case 3:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Upright / No Wrapping',3 => 'Upright / Some Wrapping',4 => 'Upright / More Wrapping',5 => 'Upright / Extreme Wrapping',6 => 'Oblique / No Wrapping',7 => 'Oblique / Some Wrapping',8 => 'Oblique / More Wrapping',9 => 'Oblique / Extreme Wrapping',10 => 'Exaggerated / No Wrapping',11 => 'Exaggerated / Some Wrapping',12 => 'Exaggerated / More Wrapping',13 => 'Exaggerated / Extreme Wrapping');
							break;
						case 4:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'None',3 => 'Inline',4 => 'Outline',5 => 'Engraved (Multiple Lines)',6 => 'Shadow',7 => 'Relief',8 => 'Backdrop');
							break;
						case 5:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'No Width',3 => 'Exceptionally Wide',4 => 'Super Wide',5 => 'Very Wide',6 => 'Wide',7 => 'Normal',8 => 'Narrow',9 => 'Very Narrow');
							break;
					}
					break;
				case 8:
					switch($FamilyType) {
						case 2:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Standard/Trimmed',3 => 'Standard/Pointed',4 => 'Standard/Serifed',5 => 'High/Trimmed',6 => 'High/Pointed',7 => 'High/Serifed',8 => 'Constant/Trimmed',9 => 'Constant/Pointed',10 => 'Constant/Serifed',11 => 'Low/Trimmed',12 => 'Low/Pointed',13 => 'Low/Serifed');
							break;
						case 3:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'None / No loops',3 => 'None / Closed loops',4 => 'None / Open loops',5 => 'Sharp / No loops',6 => 'Sharp / Closed loops',7 => 'Sharp / Open loops',8 => 'Tapered / No loops',9 => 'Tapered / Closed loops',10 => 'Tapered / Open loops',11 => 'Round / No loops',12 => 'Round / Closed loops',13 => 'Round / Open loops');
							break;
						case 4:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Standard',3 => 'Square',4 => 'Multiple Segment',5 => 'Deco (E,M,S) Waco midlines',6 => 'Uneven Weighting',7 => 'Diverse Arms',8 => 'Diverse Forms',9 => 'Lombardic Forms',10 => 'Upper Case in Lower Case',11 => 'Implied Topology',12 => 'Horseshoe E and A',13 => 'Cursive',14 => 'Blackletter',15 => 'Swash Variance');
							break;
						case 5:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'No Width',3 => 'Exceptionally Wide',4 => 'Super Wide',5 => 'Very Wide',6 => 'Wide',7 => 'Normal',8 => 'Narrow',9 => 'Very Narrow');
							break;
					}
					break;
				case 9:
					switch($FamilyType) {
						case 2:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Constant/Small',3 => 'Constant/Standard',4 => 'Constant/Large',5 => 'Ducking/Small',6 => 'Ducking/Standard',7 => 'Ducking/Large');
							break;
						case 3:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Very Low',3 => 'Low',4 => 'Medium',5 => 'High',6 => 'Very High');
							break;
						case 4:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'Extended Collection',3 => 'Litterals',4 => 'No Lower Case',5 => 'Small Caps');
							break;
						case 5:
							$FamilySubType_str = array(0 => 'Any',1 => 'No Fit',2 => 'No Width',3 => 'Exceptionally Wide',4 => 'Super Wide',5 => 'Very Wide',6 => 'Wide',7 => 'Normal',8 => 'Narrow',9 => 'Very Narrow');
							break;
					}
					break;
			}
			if(array_key_exists($FamilySubType,$FamilySubType_str)) {
				return $FamilySubType_str[$FamilySubType];
			} else {
				return 'unknown family sub-type ('.$FamilyType.')';
			}
		}
	}
}

