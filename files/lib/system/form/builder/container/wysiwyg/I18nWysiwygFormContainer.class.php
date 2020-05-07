<?php
namespace wcf\system\form\builder\container\wysiwyg;
use wcf\system\event\EventHandler;
use wcf\system\form\builder\button\wysiwyg\WysiwygPreviewFormButton;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\field\wysiwyg\I18nWysiwygFormField;
use wcf\system\form\builder\field\wysiwyg\WysiwygAttachmentFormField;
use wcf\system\Regex;

class I18nWysiwygFormContainer extends WysiwygFormContainer {
    /**
	 * actual wysiwyg form field
	 * @var	I18nWysiwygFormField
	 */
	protected $wysiwygField;
	
	/**
	 * pattern for the language item used to save the i18n values
	 * @var	null|string
	 */
	protected $messageLanguageItemPattern;

	public function messageLanguageItemPattern($pattern) {
		if (!Regex::compile($pattern)->isValid()) {
			throw new \InvalidArgumentException("Given pattern is invalid.");
		}
		
		$this->messageLanguageItemPattern = $pattern;
		
		return $this;
	}
    
    public function populate() {
		FormContainer::populate();
		
		$this->wysiwygField = I18nWysiwygFormField::create($this->wysiwygId)
			->objectType($this->messageObjectType)
			->minimumLength($this->getMinimumLength())
			->maximumLength($this->getMaximumLength())
			->i18n()
			->languageItemPattern($this->messageLanguageItemPattern)
			->required($this->isRequired())
			->supportAttachments($this->attachmentData !== null)
			->supportMentions($this->supportMentions)
			->supportQuotes($this->supportQuotes);
		if ($this->quoteData !== null) {
			$this->wysiwygField->quoteData(
				$this->quoteData['objectType'],
				$this->quoteData['actionClass'],
				$this->quoteData['selectors']
			);
		}
		$this->smiliesContainer = WysiwygSmileyFormContainer::create($this->wysiwygId . 'SmiliesTab')
			->wysiwygId($this->getWysiwygId())
			->label('wcf.message.smilies')
			->available($this->supportSmilies);
		$this->attachmentField = WysiwygAttachmentFormField::create($this->wysiwygId . 'Attachments')
			->wysiwygId($this->getWysiwygId());
		$this->settingsContainer = FormContainer::create($this->wysiwygId . 'SettingsContainer')
			->appendChildren($this->settingsNodes);
		$this->pollContainer = WysiwygPollFormContainer::create($this->wysiwygId . 'PollContainer')
			->wysiwygId($this->getWysiwygId());
		if ($this->pollObjectType) {
			$this->pollContainer->objectType($this->pollObjectType);
		}
		
		$this->appendChildren([
			$this->wysiwygField,
			WysiwygTabMenuFormContainer::create($this->wysiwygId . 'Tabs')
				->attribute('data-preselect', $this->getPreselect())
				->attribute('data-wysiwyg-container-id', $this->wysiwygId)
				->useAnchors(false)
				->appendChildren([
					$this->smiliesContainer,
					
					TabFormContainer::create($this->wysiwygId . 'AttachmentsTab')
						->addClass('formAttachmentContent')
						->label('wcf.attachment.attachments')
						->appendChild(
							FormContainer::create($this->wysiwygId . 'AttachmentsContainer')
								->appendChild($this->attachmentField)
						),
					
					TabFormContainer::create($this->wysiwygId . 'SettingsTab')
						->label('wcf.message.settings')
						->appendChild($this->settingsContainer)
						->available(MODULE_SMILEY),
					
					TabFormContainer::create($this->wysiwygId . 'PollTab')
						->label('wcf.poll.management')
						->appendChild($this->pollContainer)
				])
		]);
		
		if ($this->attachmentData !== null) {
			$this->setAttachmentHandler();
		}
		
		$this->getDocument()->addButton(
			WysiwygPreviewFormButton::create($this->getWysiwygId() . 'PreviewButton')
				->objectType($this->messageObjectType)
				->wysiwygId($this->getWysiwygId())
				->objectId($this->getObjectId())
		);
		
		EventHandler::getInstance()->fireAction($this, 'populate');
	}
}
