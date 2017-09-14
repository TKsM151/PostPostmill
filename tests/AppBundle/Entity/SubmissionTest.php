<?php

namespace Raddit\Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use Raddit\AppBundle\Entity\Exception\BannedFromForumException;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\ForumBan;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Entity\UserFlags;
use Raddit\AppBundle\Entity\Votable;

class SubmissionTest extends TestCase {
    /**
     * @dataProvider constructorArgsProvider
     */
    public function testConstructor($title, $url, $body, $forum, $user, $ip, $sticky, $userFlag) {
        $submission = new Submission($title, $url, $body, $forum, $user, $ip, $sticky, $userFlag);

        $this->assertSame($title, $submission->getTitle());
        $this->assertSame($url, $submission->getUrl());
        $this->assertSame($body, $submission->getBody());
        $this->assertSame($forum, $submission->getForum());
        $this->assertSame($user, $submission->getUser());
        $this->assertSame($ip, $submission->getIp());
        $this->assertSame($sticky, $submission->isSticky());
        $this->assertSame($userFlag, $submission->getUserFlag());
        $this->assertInstanceOf(\DateTime::class, $submission->getTimestamp());
        $this->assertSame($submission->getTimestamp()->getTimestamp() + 1800, $submission->getRanking());
        $this->assertCount(1, $submission->getVotes());
        $this->assertSame($ip, $submission->getVotes()->first()->getIp());
        $this->assertSame($user, $submission->getVotes()->first()->getUser());
    }

    public function testBannedUserCannotCreateSubmission() {
        $user = new User();
        $forum = new Forum('a', 'a', 'a', 'a');
        $forum->addBan(new ForumBan($forum, $user, 'a', true, new User()));

        $this->expectException(BannedFromForumException::class);

        new Submission('a', null, 'a', $forum, $user, null);
    }

    public function testBannedUserCannotVote() {
        $user = new User();
        $forum = new Forum('a', 'a', 'a', 'a');
        $forum->addBan(new ForumBan($forum, $user, 'a', true, new User()));

        $submission = new Submission('a', null, 'a', $forum, new User(), null);

        $this->expectException(BannedFromForumException::class);

        $submission->vote($user, '::1', Votable::VOTE_UP);
    }

    public function constructorArgsProvider() {
        $forum = $this->createMock(Forum::class);
        $user = $this->createMock(User::class);
        $url = 'http://example.com';

        yield ['title', $url, 'body', $forum, $user, '::1', false, UserFlags::FLAG_NONE];
        yield ['title', null, 'body', $forum, $user, '::1', false, UserFlags::FLAG_NONE];
        yield ['title', $url, null, $forum, $user, '::1', false, UserFlags::FLAG_NONE];
        yield ['title', null, null, $forum, $user, null, true, UserFlags::FLAG_ADMIN];
    }
}