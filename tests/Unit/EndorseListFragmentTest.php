<?php

use PHPUnit\Framework\TestCase;

final class EndorseListFragmentTest extends TestCase
{
    public function testAjaxFragmentDoesNotOverrideThePageLoader(): void
    {
        $view = file_get_contents(__DIR__ . '/../../application/views/endorse/item.php');

        self::assertNotFalse($view);
        self::assertStringNotContainsString('function loadMoreData()', $view);
        self::assertStringNotContainsString("$('#tbody').append(data)", $view);
    }
}
