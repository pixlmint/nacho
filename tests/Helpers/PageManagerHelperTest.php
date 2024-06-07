<?php

namespace Tests\Helpers;

use Nacho\Contracts\UserHandlerInterface;
use Nacho\Helpers\AlternativeContentPageHandler;
use Nacho\Helpers\FileHelper;
use Nacho\Helpers\HookHandler;
use Nacho\Helpers\MarkdownPageHandler;
use Nacho\Helpers\MetaHelper;
use Nacho\Helpers\PageFinder;
use Nacho\Helpers\PageManager;
use Nacho\Helpers\PageSecurityHelper;
use Nacho\Models\PicoMeta;
use Nacho\Models\PicoPage;
use Nacho\Security\UserInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PageManagerHelperTest extends TestCase
{
    private FileHelper $fileHelper;
    private UserHandlerInterface $userHandler;
    private LoggerInterface $logger;
    private HookHandler $hookHandler;
    private PageFinder $pageFinder;
    private UserInterface $user;
    private MarkdownPageHandler $markdownPageHandler;
    private AlternativeContentPageHandler $alternativeContentPageHandler;

    private PageManager $pageManager;

    protected function setUp(): void
    {
        $this->metaHelper = $this->createMock(MetaHelper::class);
        $this->pageSecurityHelper = $this->createMock(PageSecurityHelper::class);
        $this->fileHelper = $this->createStub(FileHelper::class);
        $this->userHandler = $this->createMock(UserHandlerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->hookHandler = $this->createMock(HookHandler::class);
        $this->pageFinder = $this->createMock(PageFinder::class);
        $this->user = $this->createMock(UserInterface::class);
        $this->markdownPageHandler = $this->createMock(MarkdownPageHandler::class);
        $this->alternativeContentPageHandler = $this->createMock(AlternativeContentPageHandler::class);
        $this->userHandler->method('getCurrentUser')->willReturn($this->user);
        $this->pageManager = new PageManager($this->fileHelper, $this->userHandler, $this->logger, $this->hookHandler, $this->pageFinder, $this->markdownPageHandler, $this->alternativeContentPageHandler);
    }

    public function testUpdateHookCalled(): void
    {
        $mockId = '/mockpage';
        $mockEntry = $this->createMock(PicoPage::class);
        $mockEntry->id = $mockId;
        $this->pageFinder->method('readPages')->willReturn([$mockEntry]);
        $this->fileHelper->method('storePage')->willreturn(true);

        $this->hookHandler->expects($this->atLeastOnce())
            ->method('executeHook')
            ->with($this->equalTo('post_handle_update'));

        $this->pageManager->create($mockId, 'hello tests');
    }
}
