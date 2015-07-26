<?php

namespace MyBB\Core\Twig\Extensions;

use McCool\LaravelAutoPresenter\BasePresenter;
use MyBB\Core\Moderation\ArrayModerationInterface;
use MyBB\Core\Moderation\ModerationRegistry;
use MyBB\Core\Moderation\ReversibleModerationInterface;
use MyBB\Core\Permissions\PermissionChecker;
use MyBB\Core\Presenters\Moderations\ReversibleModerationPresenterInterface;

class Moderation extends \Twig_Extension
{
	/**
	 * @var ModerationRegistry
	 */
	protected $moderationRegistry;

	/**
	 * @var PermissionChecker
	 */
	protected $permissionChecker;

	/**
	 * @param ModerationRegistry $moderationRegistry
	 * @param PermissionChecker  $permissionChecker
	 */
	public function __construct(ModerationRegistry $moderationRegistry, PermissionChecker $permissionChecker)
	{
		$this->moderationRegistry = $moderationRegistry;
		$this->permissionChecker = $permissionChecker;
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'MyBB_Twig_Extensions_Moderation';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('is_reversible_moderation', [$this, 'isReversibleModeration']),
			new \Twig_SimpleFunction('is_array_moderation', [$this, 'isArrayModeration']),
			new \Twig_SimpleFunction('render_moderation_button', [$this, 'renderModerationButton'], ['is_safe' => ['html']]),
		];
	}

	/**
	 * @param object $moderation
	 *
	 * @return bool
	 */
	public function isReversibleModeration($moderation)
	{
		return $moderation instanceof ReversibleModerationInterface
			|| $moderation instanceof ReversibleModerationPresenterInterface;
	}

	/**
	 * @param object $moderation
	 *
	 * @return bool
	 */
	public function isArrayModeration($moderation)
	{
		if ($moderation instanceof BasePresenter) {
			return $moderation->getWrappedObject() instanceof ArrayModerationInterface;
		}
		return $moderation instanceof ArrayModerationInterface;
	}

	/**
	 * @param string $moderationName
	 *
	 * @return \Illuminate\View\View
	 */
	public function renderModerationButton($moderationName, $contentName, $contentId)
	{
		$moderation = $this->moderationRegistry->get($moderationName);

		if ($moderation && $this->permissionChecker->hasPermission('user', null, $moderation->getPermissionName())) {
			return view('partials.moderation.moderation_button', [
				'moderation' => $moderation,
				'content_name' => $contentName,
				'content_id' => $contentId
			]);
		}
	}
}
