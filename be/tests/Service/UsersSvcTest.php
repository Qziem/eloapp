<?php


namespace Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Model\Factory\GameFactory;
use Model\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Service\UsersSvc;

class UsersSvcTest extends TestCase
{
    /** @var UsersSvc */
    private $service;

    /** @var MockObject|EntityManagerInterface */
    private $em;

    /** @var MockObject|UserRepository */
    private $userRepository;

    /** @var MockObject|GameFactory */
    private $gameFactory;

    /**
     * @throws \ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->gameFactory = $this->createMock(GameFactory::class);

        $this->service = new UsersSvc(
            $this->em,
            $this->userRepository,
            $this->gameFactory
        );
    }

    public function testUserAdding(): void
    {
        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');

        $this->service->addUser([
            'code' => 'TES',
            'name' => 'Test',
        ]);
    }
}