<?php

declare(strict_types=1);

/*
 * This file is part of the Extension "sf_event_mgt" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace DERHANSEN\SfEventMgt\Tests\Unit\ViewHelpers\Registration\Field;

use DERHANSEN\SfEventMgt\Domain\Model\Registration\Field;
use DERHANSEN\SfEventMgt\ViewHelpers\Registration\Field\PrefillMultiValueFieldViewHelper;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PrefillMultiValueFieldViewHelperTest extends UnitTestCase
{
    public function viewHelperReturnsExpectedResultIfNoOriginalRequestDataProvider(): array
    {
        return [
            'Default value selected' => [
                'Default',
                'Default',
                true,
            ],
            'Default value not selected' => [
                'Default',
                'Foo',
                false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider viewHelperReturnsExpectedResultIfNoOriginalRequestDataProvider
     */
    public function viewHelperReturnsExpectedResultIfNoOriginalRequest(
        string $defaultValue,
        string $currentValue,
        bool $expected
    ): void {
        $field = new Field();
        $field->setDefaultValue($defaultValue);

        $extbaseRequestParameters = $this->createMock(ExtbaseRequestParameters::class);
        $request = $this->createMock(Request::class);
        $request->expects(self::once())->method('getAttribute')->with('extbase')->willReturn($extbaseRequestParameters);
        $renderingContext = $this->createMock(RenderingContext::class);
        $renderingContext->expects(self::any())->method('getRequest')->willReturn($request);

        $viewHelper = new PrefillMultiValueFieldViewHelper();
        $viewHelper->setRenderingContext($renderingContext);
        $viewHelper->setArguments(['registrationField' => $field, 'currentValue' => $currentValue]);

        self::assertEquals($expected, $viewHelper->render());
    }

    public function viewHelperReturnsSubmittedValueIfOriginalRequestExistDataProvider(): array
    {
        return [
            'submitted value is field value for string' => [
                1,
                1,
                'option1',
                'option1',
                true,
            ],
            'submitted value is field value for array' => [
                1,
                1,
                'option1',
                ['option1', 'option2'],
                true,
            ],
            'submitted value is not field value for array' => [
                1,
                1,
                'option3',
                ['option1', 'option2'],
                false,
            ],
            'submitted registration field uid is not registration field uid' => [
                1,
                2,
                'option1',
                ['option1', 'option2'],
                false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider viewHelperReturnsSubmittedValueIfOriginalRequestExistDataProvider
     */
    public function viewHelperReturnsExpectedValueIfOriginalRequestExist(
        int $submittedRegistrationFieldUid,
        int $registrationFieldUid,
        string $currentValue,
        mixed $fieldValue,
        bool $expected
    ): void {
        $field = $this->getMockBuilder(Field::class)->getMock();
        $field->expects(self::any())->method('getUid')->willReturn($registrationFieldUid);

        $submittedData = [
            'tx_sfeventmgt_pievent' => [
                'registration' => [
                    'fields' => [
                        $submittedRegistrationFieldUid => $fieldValue,
                    ],
                ],
            ],
        ];

        $originalRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $originalRequest->expects(self::any())->method('getControllerExtensionName')->willReturn('SfEventMgt');
        $originalRequest->expects(self::any())->method('getPluginName')->willReturn('Pievent');
        $originalRequest->expects(self::any())->method('getParsedBody')->willReturn($submittedData);

        $extbaseRequestParameters = $this->createMock(ExtbaseRequestParameters::class);
        $extbaseRequestParameters->expects(self::once())->method('getOriginalRequest')->willReturn($originalRequest);
        $request = $this->createMock(Request::class);
        $request->expects(self::once())->method('getAttribute')->with('extbase')->willReturn($extbaseRequestParameters);
        $renderingContext = $this->createMock(RenderingContext::class);
        $renderingContext->expects(self::any())->method('getRequest')->willReturn($request);

        $renderingContext = $this->getMockBuilder(RenderingContext::class)->disableOriginalConstructor()->getMock();
        $renderingContext->expects(self::any())->method('getRequest')->willReturn($request);

        $viewHelper = new PrefillMultiValueFieldViewHelper();
        $viewHelper->setRenderingContext($renderingContext);
        $viewHelper->setArguments(['registrationField' => $field, 'currentValue' => $currentValue]);

        self::assertEquals($expected, $viewHelper->render());
    }

    /**
     * @test
     */
    public function viewHelperReturnsFalseIfOriginalRequestHasNoRegistrationFieldValues(): void
    {
        $field = $this->getMockBuilder(Field::class)->getMock();
        $field->expects(self::any())->method('getUid')->willReturn(1);

        $submittedData = [
            'tx_sfeventmgt_pievent' => [
                'registration' => [
                    'fields' => [],
                ],
            ],
        ];

        $originalRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $originalRequest->expects(self::any())->method('getControllerExtensionName')->willReturn('SfEventMgt');
        $originalRequest->expects(self::any())->method('getPluginName')->willReturn('Pievent');
        $originalRequest->expects(self::any())->method('getParsedBody')->willReturn($submittedData);

        $extbaseRequestParameters = $this->createMock(ExtbaseRequestParameters::class);
        $extbaseRequestParameters->expects(self::once())->method('getOriginalRequest')->willReturn($originalRequest);
        $request = $this->createMock(Request::class);
        $request->expects(self::once())->method('getAttribute')->with('extbase')->willReturn($extbaseRequestParameters);
        $renderingContext = $this->createMock(RenderingContext::class);
        $renderingContext->expects(self::any())->method('getRequest')->willReturn($request);

        $renderingContext = $this->getMockBuilder(RenderingContext::class)->disableOriginalConstructor()->getMock();
        $renderingContext->expects(self::any())->method('getRequest')->willReturn($request);

        $viewHelper = new PrefillMultiValueFieldViewHelper();
        $viewHelper->setRenderingContext($renderingContext);
        $viewHelper->setArguments(['registrationField' => $field, 'currentValue' => 'foo']);

        self::assertFalse($viewHelper->render());
    }
}
