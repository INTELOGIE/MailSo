<?php

namespace MailSoTests;

class EmailTest extends \PHPUnit_Framework_TestCase
{
	public function testNewInstance()
	{
		$oMail = \MailSo\Mime\Email::NewInstance('admin@example.com', 'Administrator', 'Remark');
		$this->assertEquals('admin@example.com', $oMail->GetEmail());
		$this->assertEquals('Administrator', $oMail->GetDisplayName());
		$this->assertEquals('Remark', $oMail->GetRemark());
		$this->assertEquals('admin', $oMail->GetAccountName());
		$this->assertEquals('example.com', $oMail->GetDomain());
		$this->assertEquals('"Administrator" <admin@example.com> (Remark)', $oMail->ToString());
		$this->assertEquals(array('Administrator', 'admin@example.com', 'Remark'), $oMail->ToArray());
	}

	public function testNewInstance1()
	{
		$oMail = \MailSo\Mime\Email::NewInstance('admin@example.com');
		$this->assertEquals('admin@example.com', $oMail->GetEmail());
		$this->assertEquals('', $oMail->GetDisplayName());
		$this->assertEquals('', $oMail->GetRemark());
		$this->assertEquals('admin@example.com', $oMail->ToString());
		$this->assertEquals(array('', 'admin@example.com', ''), $oMail->ToArray());
	}

	public function testNewInstance2()
	{
		$oMail = \MailSo\Mime\Email::NewInstance('admin@example.com', 'Administrator');
		$this->assertEquals('admin@example.com', $oMail->GetEmail());
		$this->assertEquals('Administrator', $oMail->GetDisplayName());
		$this->assertEquals('', $oMail->GetRemark());
		$this->assertEquals('"Administrator" <admin@example.com>', $oMail->ToString());
		$this->assertEquals(array('Administrator', 'admin@example.com', ''), $oMail->ToArray());
	}

	public function testNewInstance3()
	{
		$oMail = \MailSo\Mime\Email::NewInstance('admin@example.com', '', 'Remark');
		$this->assertEquals('admin@example.com', $oMail->GetEmail());
		$this->assertEquals('', $oMail->GetDisplayName());
		$this->assertEquals('Remark', $oMail->GetRemark());
		$this->assertEquals('<admin@example.com> (Remark)', $oMail->ToString());
		$this->assertEquals(array('', 'admin@example.com', 'Remark'), $oMail->ToArray());
	}

	/**
	 * @expectedException \MailSo\Base\Exceptions\InvalidArgumentException
	 */
	public function testNewInstance4()
	{
		$oMail = \MailSo\Mime\Email::NewInstance('');
	}

	public function testParse1()
	{
		$oMail = \MailSo\Mime\Email::Parse('help@example.com');
		$this->assertEquals('help@example.com', $oMail->GetEmail());

		$oMail = \MailSo\Mime\Email::Parse('<help@example.com>');
		$this->assertEquals('help@example.com', $oMail->GetEmail());
	}

	public function testParse2()
	{
		$oMail = \MailSo\Mime\Email::Parse('"Тест" <help@example.com> (Ремарка)');
		$this->assertEquals('"Тест" <help@example.com> (Ремарка)', $oMail->ToString());
	}

	public static function providerForParse()
	{
		return array(
			array('test <help@example.com>',
				array('test', 'help@example.com', '')),
			array('test<help@example.com>',
				array('test', 'help@example.com', '')),
			array('test< help@example.com >',
				array('test', 'help@example.com', '')),
			array('<help@example.com> (Remark)',
				array('', 'help@example.com', 'Remark')),
			array('"New \" Admin" <help@example.com> (Rem)',
				array('New " Admin', 'help@example.com', 'Rem')),
			array('"Тест" <help@example.com> (Ремарка)',
				array('Тест', 'help@example.com', 'Ремарка')),
			array('Microsoft Outlook<MicrosoftExchange329e71ec88ae4615bbc36ab6ce41109e@PPTH.PRIVATE>',
				array('Microsoft Outlook', 'MicrosoftExchange329e71ec88ae4615bbc36ab6ce41109e@PPTH.PRIVATE', '')),
		);
	}

	/**
     * @dataProvider providerForParse
     */
	public function testParseWithProvider($sValue, $aResult)
	{
		$oMail = \MailSo\Mime\Email::Parse($sValue);
		$this->assertEquals($aResult, $oMail->ToArray());
	}

	/**
	 * @expectedException \MailSo\Base\Exceptions\InvalidArgumentException
	 */
	public function testParse5()
	{
		$oMail = \MailSo\Mime\Email::Parse('');
	}

	/**
	 * @expectedException \MailSo\Base\Exceptions\InvalidArgumentException
	 */
	public function testParse6()
	{
		$oMail = \MailSo\Mime\Email::Parse('example.com');
	}
}
