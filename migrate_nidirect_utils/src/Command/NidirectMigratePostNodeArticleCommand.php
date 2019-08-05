<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;

/**
 * Class NidirectMigratePostNodeArticleCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostNodeArticleCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:article')
      ->setDescription($this->trans('commands.nidirect.migrate.post.article.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // List of node ID's and title for deletion.
    // Array value is purely for reference.
    $nodes = [
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

    $this->getIo()->info('Attempting to remove existing money advice service articles.');

    $entTypeManager = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $entTypeManager->loadMultiple(array_keys($nodes));
    $entTypeManager->delete($entities);

    // Try and load the entities again to ensure they don't exist.
    $entities = $entTypeManager->loadMultiple(array_keys($nodes));

    if (is_array($entities) && count($entities) == 0) {
      $this->getIo()->info($this->trans('commands.nidirect.migrate.post.article.messages.success'));
    }
    else {
      $this->getIo()->info($this->trans('commands.nidirect.migrate.post.article.messages.failure'));
      return -1;
    }
  }

}
