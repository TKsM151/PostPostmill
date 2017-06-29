<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="comments")
 */
class Comment extends Votable {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="The comment must not be empty.")
     * @Assert\Regex("/[[:graph:]]/u", message="The comment must not be empty.")
     * @Assert\Length(max=10000)
     *
     * @var string
     */
    private $body;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="comments")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Submission", inversedBy="comments")
     *
     * @var Submission
     */
    private $submission;

    /**
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="children")
     *
     * @var Comment|null
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="parent", cascade={"remove"})
     *
     * @var Comment[]|Collection
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="CommentVote", mappedBy="comment", fetch="EAGER", cascade={"persist", "remove"})
     *
     * @var CommentVote[]|Collection
     */
    private $votes;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @var bool
     */
    private $softDeleted = false;

    /**
     * @ORM\Column(type="inet", nullable=true)
     *
     * @var string|null
     */
    private $ip;

    /**
     * @ORM\OneToMany(targetEntity="CommentNotification", mappedBy="comment", cascade={"persist", "remove"})
     *
     * @var CommentNotification[]|Collection|Selectable
     */
    private $notifications;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * @var \DateTime|null
     */
    private $editedAt;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $moderated = false;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     *
     * @var int
     */
    private $userFlag = 0;

    /**
     * Creates a new comment with an implicit upvote from the comment author.
     *
     * @param Submission   $submission
     * @param User         $user
     * @param Comment|null $parent
     *
     * @return Comment
     */
    public static function create(Submission $submission, User $user, Comment $parent = null) {
        $comment = new self();
        $comment->user = $user;
        $comment->submission = $submission;
        $comment->parent = $parent;

        $vote = new CommentVote();
        $vote->setUser($user);
        $vote->setComment($comment);
        $vote->setUpvote(true);

        $comment->votes->add($vote);

        return $comment;
    }

    public function __construct() {
        $this->timestamp = new \DateTime('@'.time());
        $this->children = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body) {
        $this->body = $body;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp(\DateTime $timestamp) {
        $this->timestamp = $timestamp;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * @return Submission
     */
    public function getSubmission() {
        return $this->submission;
    }

    /**
     * @param Submission $submission
     */
    public function setSubmission($submission) {
        $this->submission = $submission;
    }

    /**
     * @return Comment|null
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param Comment|null $parent
     */
    public function setParent($parent) {
        $this->parent = $parent;
    }

    /**
     * Get replies, ordered by descending net score.
     *
     * Note: This method returns an actual array and not a {@link Collection}.
     *
     * @return Comment[]
     */
    public function getChildren() {
        $children = $this->children->toArray();

        if ($children) {
            usort($children, [$this, 'descendingNetScoreCmp']);
        }

        return $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getVotes() {
        return $this->votes;
    }

    /**
     * {@inheritdoc}
     */
    public function createVote() {
        $vote = new CommentVote();
        $vote->setComment($this);

        return $vote;
    }

    /**
     * @return bool
     */
    public function isSoftDeleted() {
        return $this->softDeleted;
    }

    /**
     * @param bool $softDeleted
     */
    public function setSoftDeleted($softDeleted) {
        $this->softDeleted = $softDeleted;
    }

    /**
     * Delete a comment without deleting its replies.
     */
    public function softDelete() {
        $this->softDeleted = true;
        $this->body = '';
    }

    /**
     * @return string|null
     */
    public function getIp() {
        return $this->ip;
    }

    /**
     * @param string|null $ip
     */
    public function setIp($ip) {
        $this->ip = $ip;
    }

    /**
     * @return Collection|Selectable|CommentNotification[]
     */
    public function getNotifications() {
        return $this->notifications;
    }

    /**
     * @return \DateTime|null
     */
    public function getEditedAt() {
        return $this->editedAt;
    }

    /**
     * @param \DateTime|null $editedAt
     */
    public function setEditedAt($editedAt) {
        $this->editedAt = $editedAt;
    }

    /**
     * @return bool
     */
    public function isModerated(): bool {
        return $this->moderated;
    }

    /**
     * @param bool $moderated
     */
    public function setModerated(bool $moderated) {
        $this->moderated = $moderated;
    }

    /**
     * @return int
     */
    public function getUserFlag(): int {
        return $this->userFlag;
    }

    /**
     * @param int $userFlag
     */
    public function setUserFlag(int $userFlag) {
        if (!in_array($userFlag, UserFlags::FLAGS, true)) {
            throw new \InvalidArgumentException('Bad flag');
        }

        $this->userFlag = $userFlag;
    }
}
