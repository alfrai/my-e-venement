<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
/**
 * liVCard class for parsing a vCard and/or creating/updating one
 *
*/
	class liVCard extends vCardBase implements ArrayAccess
	{
		/**
		 * liVCard constructor
		 *
		 * @param string Path to file, optional.
		 * @param string Raw data, optional.
		 * @param array Additional options, optional. Currently supported options:
		 *	bool Collapse: If true, elements that can have multiple values but have only a single value are returned as that value instead of an array
		 *		If false, an array is returned even if it has only one value.
		 *
		 * One of these parameters must be provided, otherwise an exception is thrown.
		 */
		public function __construct($Path = false, $RawData = false, array $Options = null)
		{
			$this->setData($Path, $RawData, $Options);
	  }
	  
	  public function setOptions(array $options = null)
	  {
			if ($options)
			{
				$this->Options = array_merge($this->Options, $options);
			}
			return $this;
	  }
	  
	  /**
	   * Set or Reset Data
	   *
		 * @param string Path to file, optional.
		 * @param string Raw data, optional.
		 * @return vCard this
		 */
	  protected function setData($Path, $RawData, array $Options = null)
	  {
			// Checking preconditions for the parser.
			// If path is given, the file should be accessible.
			// If raw data is given, it is taken as it is.
			// In both cases the real content is put in $this -> RawData
			if ($Path)
			{
				if (!is_readable($Path))
				{
					throw new Exception('vCard: Path not accessible ('.$Path.')');
				}

				$this -> Path = $Path;
				$this -> RawData = file_get_contents($this -> Path);
			}
			elseif ($RawData)
			{
				$this -> RawData = $RawData;
			}
			else
			{
				//throw new Exception('vCard: No content provided');
				// Not necessary anymore as possibility to create vCards is added
			}

			if (!$this -> Path && !$this -> RawData)
			{
				return true;
			}

      $this->setOptions($Options)->reset();
	    
			// Counting the begin/end separators. If there aren't any or the count doesn't match, there is a problem with the file.
			// If there is only one, this is a single vCard, if more, multiple vCards are combined.
			$Matches = array();
			$vCardBeginCount = preg_match_all('{^BEGIN\:VCARD}miS', $this -> RawData, $Matches);
			$vCardEndCount = preg_match_all('{^END\:VCARD}miS', $this -> RawData, $Matches);

			if (($vCardBeginCount != $vCardEndCount) || !$vCardBeginCount)
			{
				$this -> Mode = vCardBase::MODE_ERROR;
				throw new Exception('vCard: invalid vCard');
			}

			$this -> Mode = $vCardBeginCount == 1 ? vCardBase::MODE_SINGLE : vCardBase::MODE_MULTIPLE;

			// Removing/changing inappropriate newlines, i.e., all CRs or multiple newlines are changed to a single newline
			$this -> RawData = str_replace("\r", "\n", $this -> RawData);
			$this -> RawData = preg_replace('{(\n+)}', "\n", $this -> RawData);

			// In multiple card mode the raw text is split at card beginning markers and each
			//	fragment is parsed in a separate vCardBase object.
			if ($this -> Mode == self::MODE_MULTIPLE)
			{
				$this -> RawData = explode('BEGIN:VCARD', $this -> RawData);
				$this -> RawData = array_filter($this -> RawData);

				foreach ($this -> RawData as $SinglevCardRawData)
				{
					// Prepending "BEGIN:VCARD" to the raw string because we exploded on that one.
					// If there won't be the BEGIN marker in the new object, it will fail.
					$SinglevCardRawData = 'BEGIN:VCARD'."\n".$SinglevCardRawData;

					$ClassName = get_class($this);
					$this -> Data[] = new $ClassName(false, $SinglevCardRawData);
				}
			}
			else
			{
				// Protect the BASE64 final = sign (detected by the line beginning with whitespace), otherwise the next replace will get rid of it
				$this -> RawData = preg_replace('{(\n\s.+)=(\n)}', '$1-base64=-$2', $this -> RawData);

				// Joining multiple lines that are split with a hard wrap and indicated by an equals sign at the end of line
				// (quoted-printable-encoded values in v2.1 vCards)
				$this -> RawData = str_replace("=\n", '', $this -> RawData);

				// Joining multiple lines that are split with a soft wrap (space or tab on the beginning of the next line
				$this -> RawData = str_replace(array("\n ", "\n\t"), '-wrap-', $this -> RawData);

				// Restoring the BASE64 final equals sign (see a few lines above)
				$this -> RawData = str_replace("-base64=-\n", "=\n", $this -> RawData);

				$Lines = explode("\n", $this -> RawData);

				foreach ($Lines as $Line)
				{
					// Lines without colons are skipped because, most likely, they contain no data.
					if (strpos($Line, ':') === false)
					{
						continue;
					}

					// Each line is split into two parts. The key contains the element name and additional parameters, if present,
					//	value is just the value
					list($Key, $Value) = explode(':', $Line, 2);

					// Key is transformed to lowercase because, even though the element and parameter names are written in uppercase,
					//	it is quite possible that they will be in lower- or mixed case.
					// The key is trimmed to allow for non-significant WSP characters as allowed by v2.1
					$Key = strtolower(trim(self::Unescape($Key)));

					// These two lines can be skipped as they aren't necessary at all.
					if ($Key == 'begin' || $Key == 'end')
					{
						continue;
					}

					if ((strpos($Key, 'agent') === 0) && (stripos($Value, 'begin:vcard') !== false))
					{
						$ClassName = get_class($this);
						$Value = new $ClassName(false, str_replace('-wrap-', "\n", $Value));
						if (!isset($this -> Data[$Key]))
						{
							$this -> Data[$Key] = array();
						}
						$this -> Data[$Key][] = $Value;
						continue;
					}
					else
					{
						$Value = str_replace('-wrap-', '', $Value);
					}

					$Value = trim(self::Unescape($Value));
					$Type = array();

					// Here additional parameters are parsed
					$KeyParts = explode(';', $Key);
					$Key = $KeyParts[0];
					$Encoding = false;

					if (strpos($Key, 'item') === 0)
					{
						$TmpKey = explode('.', $Key, 2);
						$Key = $TmpKey[1];
						$ItemIndex = (int)str_ireplace('item', '', $TmpKey[0]);
					}

					if (count($KeyParts) > 1)
					{
						$Parameters = self::ParseParameters($Key, array_slice($KeyParts, 1));

						foreach ($Parameters as $ParamKey => $ParamValue)
						{
							switch ($ParamKey)
							{
								case 'encoding':
									$Encoding = $ParamValue;
									if (in_array($ParamValue, array('b', 'base64')))
									{
										//$Value = base64_decode($Value);
									}
									elseif ($ParamValue == 'quoted-printable') // v2.1
									{
										$Value = quoted_printable_decode($Value);
									}
									break;
								case 'charset': // v2.1
									if ($ParamValue != 'utf-8' && $ParamValue != 'utf8')
									{
										$Value = mb_convert_encoding($Value, 'UTF-8', $ParamValue);
									}
									break;
								case 'type':
									$Type = $ParamValue;
									break;
							}
						}
					}

					// Checking files for colon-separated additional parameters (Apple's Address Book does this), for example, "X-ABCROP-RECTANGLE" for photos
					if (in_array($Key, self::$Spec_FileElements) && isset($Parameters['encoding']) && in_array($Parameters['encoding'], array('b', 'base64')))
					{
						// If colon is present in the value, it must contain Address Book parameters
						//	(colon is an invalid character for base64 so it shouldn't appear in valid files)
						if (strpos($Value, ':') !== false)
						{
							$Value = explode(':', $Value);
							$Value = array_pop($Value);
						}
					}

					// Values are parsed according to their type
					if (isset(self::$Spec_StructuredElements[$Key]))
					{
						$Value = self::ParseStructuredValue($Value, $Key);
						if ($Type)
						{
							$Value['Type'] = $Type;
						}
					}
					else
					{
						if (in_array($Key, self::$Spec_MultipleValueElements))
						{
							$Value = self::ParseMultipleTextValue($Value, $Key);
						}

						if ($Type)
						{
							$Value = array(
								'Value' => $Value,
								'Type' => $Type
							);
						}
					}

					if (is_array($Value) && $Encoding)
					{
						$Value['Encoding'] = $Encoding;
					}

					if (!isset($this -> Data[$Key]))
					{
						$this -> Data[$Key] = array();
					}

					$this -> Data[$Key][] = $Value;
				}
			}
			
			return $this;
		}
		
		public function offsetExists( $offset )
	  {
	    return isset($this->Data[$offset]);
	  }
	  public function offsetGet( $offset )
	  {
	    if ( !isset($this[$offset]) )
	      return false;
	    
	    $values = array_values($this->Data[$offset]);
	    return count($values) == 1 ? $values[0] : $this->Data[$offset];
	  }
	  public function offsetUnset( $offset )
	  {
	    unset($this->Data[$offset]);
	  }
	  public function offsetSet( $offset, $value )
	  {
	    if ( !isset($this->Data[$offset]) )
	      $this->Data[$offset] = array($value);
	    else
  	    $this->Data[$offset][] = $value;
	  }
	  
	  public static function realNLToVcfNL($str)
	  {
	    return str_replace(array("\n","\r"),array('\n',''),$str);
	  }
	  public static function vcfNLToRealNL($str)
	  {
	    return str_replace(array('\n'),array("\r\n"),$str);
	  }
	  
	  /**
	   * function reset() permits to start on a new Data
	   *
	   **/
	  public function reset()
	  {
	    $this->Data = array();
	    return $this;
	  }
  }
