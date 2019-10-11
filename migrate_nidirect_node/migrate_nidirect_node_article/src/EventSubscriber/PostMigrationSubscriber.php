<?php

namespace Drupal\migrate_nidirect_node_article\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Class PostMigrationSubscriber.
 *
 * Post Migrate processes.
 */
class PostMigrationSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Logger\LoggerChannelFactory definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Array of nodes that should be removed from the site.
   *
   * @var array
   */
  protected $nodes;

  /**
   * PostMigrationSubscriber constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactory $logger) {
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger->get('migrate_nidirect_node_article');

    $this->nodes = [
      4779 => "Beginner’s guide to managing your money",
      4780 => "Borrowing and credit basics",
      4781 => "Borrowing from a credit union",
      5433 => "Living on a budget",
      4783 => "Catalogue credit or shopping accounts",
      10190 => "Saving for your children",
      9571 => "Mortgage advice – Should you get a mortgage adviser?",
      4786 => "Financial mis-selling – what to do if you're affected",
      7675 => "Compensation if your bank or building society goes bust",
      4788 => "How to choose the right bank account",
      4791 => "How to make an insurance complaint",
      4789 => "How to get a mortgage if you’re struggling",
      4790 => "Investing – beginner’s guide",
      4792 => "Mortgages – a beginner’s guide",
      4793 => "National Savings & Investments (NS&I)",
      4794 => "Overdrafts explained",
      4796 => "Review your savings and investments",
      4797 => "Secured and unsecured borrowing explained",
      4798 => "Should you manage money jointly or separately?",
      4799 => "Should you pay off your mortgage early?",
      4800 => "Should you save, or pay off loans and cards?",
      4801 => "Sort out a money problem or make a complaint",
      4802 => "Refused credit or refused a loan – what you can do",
      4804 => "Why it pays to save regularly",
      4805 => "How to open, switch or close your bank account",
    ];
}

  /**
   * Get subscribed events.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_IMPORT][] = ['onMigratePostImport'];
    return $events;
  }

  /**
   * Handle post import migration event.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event object.
   */
  public function onMigratePostImport(MigrateImportEvent $event) {
    $event_id = $event->getMigration()->getBaseId();

    if ($event_id == 'node_article' || $event_id == 'node_article_revision') {

      $this->logger->notice('Post migrate processing.');

      $entities = $this->entityTypeManager->getStorage("node")->loadMultiple(array_keys($this->nodes));
      $this->entityTypeManager->getStorage("node")->delete($entities);

      // Try and load the entities again to ensure they don't exist.
      $entities = $this->entityTypeManager->getStorage("node")->loadMultiple(array_keys($this->nodes));

      if (is_array($entities) && count($entities) == 0) {
        $this->logger->notice('Post migrate processing: Success');
      }
      else {
        $this->logger->notice('Post migrate processing: Failure');
      }

    }

  }

}
