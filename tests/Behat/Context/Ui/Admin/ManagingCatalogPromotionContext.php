<?php

namespace Tests\Acme\SyliusCatalogPromotionBundle\Behat\Context\Ui\Admin;

use Behat\Behat\Tester\Exception\PendingException;
use Acme\SyliusCatalogPromotionBundle\Entity\CatalogPromotionInterface;
use Behat\Behat\Context\Context;
use Sylius\Component\Core\Model\ChannelInterface;
use Tests\Acme\SyliusCatalogPromotionBundle\Behat\Page\Admin\CreatePageInterface;
use Tests\Acme\SyliusCatalogPromotionBundle\Behat\Page\Admin\IndexPageInterface;
use Tests\Acme\SyliusCatalogPromotionBundle\Behat\Page\Admin\UpdatePageInterface;
use Webmozart\Assert\Assert;

final class ManagingCatalogPromotionContext implements Context
{
    /**
     * @var CreatePageInterface
     */
    private $createPage;

    /**
     * @var IndexPageInterface
     */
    private $indexPage;

    /**
     * @var UpdatePageInterface
     */
    private $updatePage;

    /**
     * @param CreatePageInterface $createPage
     * @param IndexPageInterface $indexPage
     * @param UpdatePageInterface $updatePage
     */
    public function __construct(
        CreatePageInterface $createPage,
        IndexPageInterface $indexPage,
        UpdatePageInterface $updatePage
    ) {
        $this->createPage = $createPage;
        $this->indexPage = $indexPage;
        $this->updatePage = $updatePage;
    }

    /**
     * @When I create a new catalog promotion
     */
    public function iCreateANewCatalogPromotion()
    {
        $this->createPage->open();
    }

    /**
     * @When I name it :name
     */
    public function iNameIt($name = null)
    {
        $this->createPage->nameIt($name);
    }

    /**
     * @When I specify its code as :code
     */
    public function iSpecifyItsCodeAs($code = null)
    {
        $this->createPage->specifyCode($code);
    }

    /**
     * @When I add it
     * @When I try to add it
     */
    public function iAddIt()
    {
        $this->createPage->create();
    }

    /**
     * @When I make it exclusive
     */
    public function iMakeItExclusive()
    {
        $this->createPage->makeExclusive();
    }

    /**
     * @When I make it available from :startsDate to :endsDate
     */
    public function iMakeItAvailableFromTo(\DateTime $startsDate, \DateTime $endsDate)
    {
        $this->createPage->setStartsAt($startsDate);
        $this->createPage->setEndsAt($endsDate);
    }

    /**
     * @When /^I specify the percentage discount with amount of "([^"]+%)"$/
     */
    public function iSpecifyThePercentageDiscountWithAmountOf($amount)
    {
        $this->createPage->chooseActionType('Percentage discount');
        $this->createPage->fillActionAmount('Percentage', $amount);
    }

    /**
     * @When /^I add the fixed value discount with amount of "(?:€|£|\$)([^"]+)" for "([^"]+)" channel$/
     */
    public function iAddTheFixedValueDiscountWithAmountOfForChannel($amount, $channelName)
    {
        $this->createPage->chooseActionType('Fixed discount');
        $this->createPage->fillActionAmount($channelName, $amount);
    }

    /**
     * @When I make it applicable for the :channelName channel
     */
    public function iMakeItApplicableForTheChannel($channelName)
    {
        $this->createPage->checkChannel($channelName);
    }

    /**
     * @When I browse catalog promotions
     */
    public function iBrowseCatalogPromotions()
    {
        $this->indexPage->open();
    }

    /**
     * @Then the :catalogPromotionName catalog promotion should appear in the registry
     */
    public function theCatalogPromotionShouldAppearInTheRegistry($catalogPromotionName)
    {
        $this->iBrowseCatalogPromotions();

        Assert::true($this->indexPage->isSingleResourceOnPage(['name' => $catalogPromotionName]));
    }

    /**
     * @Then the :catalogPromotion catalog promotion should be exclusive
     */
    public function thisCatalogPromotionShouldBeExclusive(CatalogPromotionInterface $catalogPromotion)
    {
        $this->iBrowseCatalogPromotions();

        Assert::true($this->indexPage->isExclusive($catalogPromotion->getCode()));
    }

    /**
     * @Then the :catalogPromotion catalog promotion should be available from :startsDate to :endsDate
     */
    public function thePromotionShouldBeAvailableFromTo(
        CatalogPromotionInterface $catalogPromotion,
        \DateTime $startsDate,
        \DateTime $endsDate
    ) {
        $this->openEditPage($catalogPromotion);

        Assert::true($this->updatePage->hasStartsAt($startsDate));
        Assert::true($this->updatePage->hasEndsAt($endsDate));
    }

    /**
     * @Then /^the ("[^"]+" catalog promotion) should give "([^"]+)%" discount$/
     */
    public function theCatalogPromotionShouldGivePercentageDiscount(CatalogPromotionInterface $catalogPromotion, $amount)
    {
        $this->openEditPage($catalogPromotion);

        Assert::eq($this->updatePage->getAmount(), $amount);
    }

    /**
     * @Then /^the ("[^"]+" catalog promotion) should give "(?:€|£|\$)([^"]+)" discount for ("[^"]+" channel)$/
     */
    public function thisCatalogPromotionShouldGiveDiscountForChannel(
        CatalogPromotionInterface $catalogPromotion,
        $amount,
        ChannelInterface $channel
    ) {
        $this->openEditPage($catalogPromotion);

        Assert::eq($this->updatePage->getValueForChannel($channel->getCode()), $amount);
    }

    /**
     * @Then the :catalogPromotion catalog promotion should be applicable for the :channelName channel
     */
    public function thePromotionShouldBeApplicableForTheChannel(CatalogPromotionInterface $catalogPromotion, $channelName)
    {
        $this->openEditPage($catalogPromotion);

        Assert::true($this->updatePage->checkChannelsState($channelName));
    }

    /**
     * @Then there should be a single catalog promotion
     */
    public function thereShouldBeASingleCatalogPromotion()
    {
        Assert::same(
            1,
            $this->indexPage->countItems(),
            'I should see %s promotions but i see only %2$s'
        );
    }

    /**
     * @Then the :catalogPromotionName catalog promotion should exist in the registry
     */
    public function theCatalogPromotionShouldExistInTheRegistry($catalogPromotionName)
    {
        Assert::true($this->indexPage->isSingleResourceOnPage(['name' => $catalogPromotionName]));
    }

    /**
     * @param CatalogPromotionInterface $catalogPromotion
     */
    private function openEditPage(CatalogPromotionInterface $catalogPromotion)
    {
        $this->updatePage->open(['id' => $catalogPromotion->getId()]);
    }

    /**
     * @Then I should be notified that a catalog promotion with this code already exists
     */
    public function iShouldBeNotifiedThatACatalogPromotionWithThisCodeAlreadyExists()
    {
        Assert::same($this->createPage->getValidationMessage('code'), 'The catalog promotion with given code already exists.');
    }

    /**
     * @Then there should still be only one catalog promotion with code :code
     */
    public function thereShouldStillBeOnlyOneCatalogPromotionWithCode($code)
    {
        $this->iBrowseCatalogPromotions();

        Assert::true($this->indexPage->isSingleResourceOnPage(['code' => $code]));
    }
}
