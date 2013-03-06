<?php

namespace MailSo\Imap;

/**
 * @category MailSo
 * @package Imap
 */
class BodyStructure
{
	/**
	 * @var string
	 */
	private $sContentType;

	/**
	 * @var string
	 */
	private $sCharset;

	/**
	 * @var array
	 */
	private $aBodyParams;

	/**
	 * @var string
	 */
	private $sContentID;

	/**
	 * @var string
	 */
	private $sDescription;

	/**
	 * @var string
	 */
	private $sMailEncodingName;

	/**
	 * @var string
	 */
	private $sDisposition;

	/**
	 * @var array
	 */
	private $aDispositionParams;

	/**
	 * @var string
	 */
	private $sFileName;

	/**
	 * @var string
	 */
	private $sLanguage;

	/**
	 * @var string
	 */
	private $sLocation;

	/**
	 * @var int
	 */
	private $iSize;

	/**
	 * @var int
	 */
	private $iTextLineCount;

	/**
	 * @var string
	 */
	private $sPartID;

	/**
	 * @var array
	 */
	private $aSubParts;

	/**
	 * @access private
	 *
	 * @param string $sContentType
	 * @param string $sCharset
	 * @param array $aBodyParams
	 * @param string $sContentID
	 * @param string $sDescription
	 * @param string $sMailEncodingName
	 * @param string $sDisposition
	 * @param array $aDispositionParams
	 * @param string $sFileName
	 * @param string $sLanguage
	 * @param string $sLocation
	 * @param int $iSize
	 * @param int $iTextLineCount
	 * @param string $sPartID
	 * @param array $aSubParts
	 */
	private function __construct($sContentType, $sCharset, $aBodyParams, $sContentID,
		$sDescription, $sMailEncodingName, $sDisposition, $aDispositionParams, $sFileName,
		$sLanguage, $sLocation, $iSize, $iTextLineCount, $sPartID, $aSubParts)
	{
		$this->sContentType = $sContentType;
		$this->sCharset = $sCharset;
		$this->aBodyParams = $aBodyParams;
		$this->sContentID = $sContentID;
		$this->sDescription = $sDescription;
		$this->sMailEncodingName = $sMailEncodingName;
		$this->sDisposition = $sDisposition;
		$this->aDispositionParams = $aDispositionParams;
		$this->sFileName = $sFileName;
		$this->sLanguage = $sLanguage;
		$this->sLocation = $sLocation;
		$this->iSize = $iSize;
		$this->iTextLineCount = $iTextLineCount;
		$this->sPartID = $sPartID;
		$this->aSubParts = $aSubParts;
	}

	/**
	 * return string
	 */
	public function MailEncodingName()
	{
		return $this->sMailEncodingName;
	}

	/**
	 * return string
	 */
	public function PartID()
	{
		return (string) $this->sPartID;
	}

	/**
	 * return string
	 */
	public function FileName()
	{
		return $this->sFileName;
	}

	/**
	 * return string
	 */
	public function ContentType()
	{
		return $this->sContentType;
	}

	/**
	 * return int
	 */
	public function Size()
	{
		return (int) $this->iSize;
	}

	/**
	 * return int
	 */
	public function EstimatedSize()
	{
		$fCoefficient = 1;
		switch (strtolower($this->MailEncodingName()))
		{
			case 'base64':
				$fCoefficient = 0.75;
				break;
			case 'quoted-printable':
				$fCoefficient = 0.44;
				break;
		}

		return (int) ($this->Size() * $fCoefficient);
	}

	/**
	 * return string
	 */
	public function Charset()
	{
		return $this->sCharset;
	}


	/**
	 * return string
	 */
	public function ContentID()
	{
		return (null === $this->sContentID) ? '' : $this->sContentID;
	}

	/**
	 * return bool
	 */
	public function IsInline()
	{
		return (null === $this->sDisposition) ? false : ('inline' === strtolower($this->sDisposition));
	}

	/**
	 * @return \MailSo\Imap\BodyStructure|null
	 */
	public function SearchPlainPart()
	{
		$oReturn = null;
		$aParts = $this->SearchByContentType('text/plain');
		foreach ($aParts as $oPart)
		{
			if (!$oPart->isAttachBodyPart())
			{
				$oReturn = $oPart;
				break;
			}
		}
		return $oReturn;
	}

	/**
	 * @return \MailSo\Imap\BodyStructure|null
	 */
	public function SearchHtmlPart()
	{
		$oReturn = null;
		$aParts = $this->SearchByContentType('text/html');
		foreach ($aParts as $oPart)
		{
			if (!$oPart->isAttachBodyPart())
			{
				$oReturn = $oPart;
				break;
			}
		}
		return $oReturn;
	}

	/**
	 * @return \MailSo\Imap\BodyStructure|null
	 */
	public function SearchHtmlOrPlainPart()
	{
		$oResult = $this->SearchHtmlPart();
		if (null === $oResult)
		{
			$oResult = $this->SearchPlainPart();
		}
		return $oResult;
	}

	/**
	 * @return string
	 */
	public function SearchCharset()
	{
		$sResult = '';

		$oPart = $this->SearchHtmlPart();
		$sResult = $oPart ? $oPart->Charset() : '';
		if (0 === strlen($sResult))
		{
			$oPart = $this->SearchPlainPart();
			$sResult = $oPart ? $oPart->Charset() : '';
		}

		if (0 === strlen($sResult))
		{
			$aParts = $this->SearchAttachmentsParts();
			foreach ($aParts as $oPart)
			{
				if (0 === strlen($sResult))
				{
					$sResult = $oPart ? $oPart->Charset() : '';
				}
				else
				{
					break;
				}
			}
		}

		return $sResult;
	}

	/**
	 * @return bool
	 */
	protected function isAttachBodyPart()
	{
		$bResult = (
//			(null !== $this->sFileName && 0 < strlen($this->sFileName)) ||
			(null !== $this->sDisposition && 'attachment' === strtolower($this->sDisposition))
		);

		if (!$bResult && null !== $this->sContentType)
		{
			$sContentType = strtolower($this->sContentType);
			$bResult = false === strpos($sContentType, 'multipart/') &&
				'text/html' !== $sContentType && 'text/plain' !== $sContentType;
		}

		return $bResult;
	}

	/**
	 * @return array
	 */
	public function SearchAttachmentsParts()
	{
		$aReturn = array();
		if ($this->isAttachBodyPart())
		{
			$aReturn[] = $this;
		}

		if (is_array($this->aSubParts) && 0 < count($this->aSubParts))
		{
			foreach ($this->aSubParts as /* @var $oSubPart \MailSo\Imap\BodyStructure */ &$oSubPart)
			{
				$aReturn = array_merge($aReturn, $oSubPart->SearchAttachmentsParts());
				unset($oSubPart);
			}
		}

		return $aReturn;
	}

	/**
	 * @param string $sContentType
	 *
	 * @return array
	 */
	public function SearchByContentType($sContentType)
	{
		$aReturn = array();
		if (strtolower($sContentType) === $this->sContentType)
		{
			$aReturn[] = $this;
		}

		if (is_array($this->aSubParts) && 0 < count($this->aSubParts))
		{
			foreach ($this->aSubParts as /* @var $oSubPart \MailSo\Imap\BodyStructure */ &$oSubPart)
			{
				$aReturn = array_merge($aReturn, $oSubPart->SearchByContentType($sContentType));
			}
		}

		return $aReturn;
	}

	/**
	 * @param string $sMimeIndex
	 *
	 * @return \MailSo\Imap\BodyStructure
	 */
	public function GetPartByMimeIndex($sMimeIndex)
	{
		$oPart = null;
		if (0 < strlen($sMimeIndex))
		{
			if ($sMimeIndex === $this->sPartID)
			{
				$oPart = $this;
			}

			if (null === $oPart && is_array($this->aSubParts) && 0 < count($this->aSubParts))
			{
				foreach ($this->aSubParts as /* @var $oSubPart \MailSo\Imap\BodyStructure */ &$oSubPart)
				{
					$oPart = $oSubPart->GetPartByMimeIndex($sMimeIndex);
					if (null !== $oPart)
					{
						break;
					}
				}
			}
		}

		return $oPart;
	}

	/**
	 * @param array $aBodyStructure
	 * @param string $sPartID = ''
	 *
	 * @return \MailSo\Imap\BodyStructure
	 */
	public static function NewInstance(array $aBodyStructure, $sPartID = '')
	{
		if (!is_array($aBodyStructure) || 2 > count($aBodyStructure))
		{
			return null;
		}
		else
		{
			$sBodyMainType = null;
			if (is_string($aBodyStructure[0]) && 'NIL' !== $aBodyStructure[0])
			{
				$sBodyMainType = $aBodyStructure[0];
			}

			$sBodySubType = null;
			$sContentType = '';
			$aSubParts = null;
			$aBodyParams = array();
			$sCharset = null;
			$sContentID = null;
			$sDescription = null;
			$sMailEncodingName = null;
			$iSize = 0;
			$iTextLineCount = 0; // valid for rfc822/message and text parts
			$iExtraItemPos = 0;  // list index of items which have no well-established position (such as 0, 1, 5, etc).

			if (null === $sBodyMainType)
			{
				// Process multipart body structure
				if (!is_array($aBodyStructure[0]))
				{
					return null;
				}
				else
				{
					$sBodyMainType = 'multipart';
					$sSubPartIDPrefix = '';
					if (0 === strlen($sPartID) || '.' === $sPartID[strlen($sPartID) - 1])
					{
						// This multi-part is root part of message.
						$sSubPartIDPrefix = $sPartID;
						$sPartID .= 'TEXT';
					}
					else if (0 < strlen($sPartID))
					{
						// This multi-part is a part of another multi-part.
						$sSubPartIDPrefix = $sPartID.'.';
					}

					$aSubParts = array();
					$iIndex = 1;

					while ($iExtraItemPos < count($aBodyStructure) && is_array($aBodyStructure[$iExtraItemPos]))
					{
						$oPart = self::NewInstance($aBodyStructure[$iExtraItemPos], $sSubPartIDPrefix.$iIndex);
						if (null === $oPart)
						{
							return null;
						}
						else
						{
							// For multipart, we have no charset info in the part itself. Thus,
							// obtain charset from nested parts.
							if ($sCharset == null)
							{
								$sCharset = $oPart->Charset();
							}

							$aSubParts[] = $oPart;
							$iExtraItemPos++;
							$iIndex++;
						}
					}
				}

				if ($iExtraItemPos < count($aBodyStructure))
				{
					if (!is_string($aBodyStructure[$iExtraItemPos]) || 'NIL' === $aBodyStructure[$iExtraItemPos])
					{
						return null;
					}

					$sBodySubType = strtolower($aBodyStructure[$iExtraItemPos]);
					$iExtraItemPos++;
				}

				if ($iExtraItemPos < count($aBodyStructure))
				{
					$sBodyParamList = $aBodyStructure[$iExtraItemPos];
					if (is_array($sBodyParamList))
					{
						$aBodyParams = self::getKeyValueListFromArrayList($sBodyParamList);
					}
				}
				$iExtraItemPos++;
			}
			else
			{
				// Process simple (singlepart) body structure
				if (7 > count($aBodyStructure))
				{
					return null;
				}

				$sBodyMainType = strtolower($sBodyMainType);
				if (!is_string($aBodyStructure[1]) || 'NIL' === $aBodyStructure[1])
				{
					return null;
				}

				$sBodySubType = strtolower($aBodyStructure[1]);

				$aBodyParamList = $aBodyStructure[2];
				if (is_array($aBodyParamList))
				{
					$aBodyParams = self::getKeyValueListFromArrayList($aBodyParamList);
					if (isset($aBodyParams['charset']))
					{
						$sCharset = $aBodyParams['charset'];
					}
				}

				if (null !== $aBodyStructure[3] && 'NIL' !== $aBodyStructure[3])
				{
					if (!is_string($aBodyStructure[3]))
					{
						return null;
					}
					$sContentID = $aBodyStructure[3];
				}

				if (null !== $aBodyStructure[4] && 'NIL' !== $aBodyStructure[4])
				{
					if (!is_string($aBodyStructure[4]))
					{
						return null;
					}
					$sDescription = $aBodyStructure[4];
				}

				if (null !== $aBodyStructure[5] && 'NIL' !== $aBodyStructure[5])
				{
					if (!is_string($aBodyStructure[5]))
					{
						return null;
					}
					$sMailEncodingName = $aBodyStructure[5];
				}

				if (is_numeric($aBodyStructure[6]))
				{
					$iSize = (int) $aBodyStructure[6];
				}
				else
				{
					$iSize = -1;
				}

				if (0 === strlen($sPartID) || '.' === $sPartID[strlen($sPartID) - 1])
				{
					// This is the only sub-part of the message (otherwise, it would be
					// one of sub-parts of a multi-part, and partID would already be fully set up).
					$sPartID .= '1';
				}

				$iExtraItemPos = 7;
				if ('text' === $sBodyMainType)
				{
					if ($iExtraItemPos < count($aBodyStructure))
					{
						if (is_numeric($aBodyStructure[$iExtraItemPos]))
						{
							$iTextLineCount = (int) $aBodyStructure[$iExtraItemPos];
						}
						else
						{
							$iTextLineCount = -1;
						}
					}
					else
					{
						$iTextLineCount = -1;
					}
					$iExtraItemPos++;
				}
				else if ('message' === $sBodyMainType && 'rfc822' === $sBodySubType)
				{
					if ($iExtraItemPos + 2 < count($aBodyStructure))
					{
						if (is_numeric($aBodyStructure[$iExtraItemPos + 2]))
						{
							$iTextLineCount = (int) $aBodyStructure[$iExtraItemPos + 2];
						}
						else
						{
							$iTextLineCount = -1;
						}
					}
					else
					{
						$iTextLineCount = -1;
					}

					$iExtraItemPos += 3;
				}

				$iExtraItemPos++;	// skip MD5 digest of the body because most mail servers leave it NIL anyway
			}

			$sContentType = $sBodyMainType.'/'.$sBodySubType;

			$sDisposition = null;
			$aDispositionParams = null;
			$sFileName = null;

			if ($iExtraItemPos < count($aBodyStructure))
			{
				$aDispList = $aBodyStructure[$iExtraItemPos];
				if (is_array($aDispList) && 1 < count($aDispList))
				{
					if (null !== $aDispList[0])
					{
						if (is_string($aDispList[0]) && 'NIL' !== $aDispList[0])
						{
							$sDisposition = $aDispList[0];
						}
						else
						{
							return null;
						}
					}
				}

				$aDispParamList = $aDispList[1];
				if (is_array($aDispParamList))
				{
					$aDispositionParams = self::getKeyValueListFromArrayList($aDispParamList);
					if (isset($aDispositionParams['filename']))
					{
						$sFileName = \MailSo\Base\Utils::DecodeHeaderValue($aDispositionParams['filename'], $sCharset);
					}
					else if (isset($aDispositionParams['filename*0*']))
					{
						$sCharset = '';
						$aFileNames = array();
						foreach ($aDispositionParams as $sName => $sValue)
						{
							$aMatches = array();
							if ('filename*0*' === $sName)
							{
								if (0 === strlen($sCharset))
								{
									$aValueParts = explode('\'\'', $sValue, 2);
									if (is_array($aValueParts) && 2 === count($aValueParts) && 0 < strlen($aValueParts[0]))
									{
										$sCharset = $aValueParts[0];
										$sValue = $aValueParts[1];
									}
								}

								$aFileNames[0] = $sValue;
							}
							else if ('filename*0*' !== $sName && preg_match('/^filename\*([0-9]+)\*$/i', $sName, $aMatches) && 0 < strlen($aMatches[1]))
							{
								$aFileNames[(int) $aMatches[1]] = $sValue;
							}
						}

						if (0 < count($aFileNames))
						{
							ksort($aFileNames, SORT_NUMERIC);
							$sFileName = implode(array_values($aFileNames));
							$sFileName = urldecode($sFileName);

							if (0 < strlen($sCharset))
							{
								$sFileName = \MailSo\Base\Utils::ConvertEncoding($sFileName,
									$sCharset, \MailSo\Base\Enumerations\Charset::UTF_8);
							}
						}
					}
				}
			}
			$iExtraItemPos++;

			$sLanguage = null;
			if ($iExtraItemPos < count($aBodyStructure))
			{
				if (null !== $aBodyStructure[$iExtraItemPos] && 'NIL' !== $aBodyStructure[$iExtraItemPos])
				{
					if (is_array($aBodyStructure[$iExtraItemPos]))
					{
						$sLanguage = implode(',', $aBodyStructure[$iExtraItemPos]);
					}
					else if (is_string($aBodyStructure[$iExtraItemPos]))
					{
						$sLanguage = $aBodyStructure[$iExtraItemPos];
					}
				}
				$iExtraItemPos++;
			}

			$sLocation = null;
			if ($iExtraItemPos < count($aBodyStructure))
			{
				if (null !== $aBodyStructure[$iExtraItemPos] && 'NIL' !== $aBodyStructure[$iExtraItemPos])
				{
					if (is_string($aBodyStructure[$iExtraItemPos]))
					{
						$sLocation = $aBodyStructure[$iExtraItemPos];
					}
				}
				$iExtraItemPos++;
			}

			return new self(
				$sContentType,
				$sCharset,
				$aBodyParams,
				$sContentID,
				$sDescription,
				$sMailEncodingName,
				$sDisposition,
				$aDispositionParams,
				$sFileName,
				$sLanguage,
				$sLocation,
				$iSize,
				$iTextLineCount,
				$sPartID,
				$aSubParts
			);
		}
	}

	/**
	 * Returns dict with key="charset" and value="US-ASCII" for array ("CHARSET" "US-ASCII").
	 * Keys are lowercased (StringDictionary itself does this), values are not altered.
	 *
	 * @param array $aList
	 *
	 * @return array
	 */
	private static function getKeyValueListFromArrayList(array $aList)
	{
		$aDict = null;
		if (0 === count($aList) % 2)
		{
			$aDict = array();
			for ($iIndex = 0, $iLen = count($aList); $iIndex < $iLen; $iIndex += 2)
			{
				if (is_string($aList[$iIndex]) && isset($aList[$iIndex + 1]) && is_string($aList[$iIndex + 1]))
				{
					$aDict[strtolower($aList[$iIndex])] = $aList[$iIndex + 1];
				}
			}
		}

		return $aDict;
	}
}
