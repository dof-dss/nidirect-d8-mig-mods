module.exports = {
    '@tags': ['nidirect-migrations', 'nidirect-article'],

    'Test whether Article content type exists': browser => {
        browser
            .drupalLogin({ name: 'admin', password: 'letmein' });

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Article');
    },

    'Test whether Article fields exist': browser => {
        browser.drupalRelativeURL('/admin/structure/types/manage/article/fields');
        browser.expect.element('#field-banner-image').to.be.present;
        browser.expect.element('#body').to.be.present;
        browser.expect.element('#comment').to.be.present;
        browser.expect.element('#field-enable-toc').to.be.present;
        browser.expect.element('#field-additional-info').to.be.present;
        browser.expect.element('#field-image').to.be.present;
        browser.expect.element('#field-meta-tags').to.be.present;
        browser.expect.element('#field-photo').to.be.present;
        browser.expect.element('#field-summary').to.be.present;
        browser.expect.element('#field-site-themes').to.be.present;
        browser.expect.element('#field-tags').to.be.present;
        browser.expect.element('#field-teaser').to.be.present;
        browser.expect.element('#field-subtheme').to.be.present;
        browser.expect.element('#field-top-level-theme').to.be.present;
    },

    'Test whether Article nodes exist': browser => {

        // See if we have any article nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=article')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Article');

        // Now test a random sample of actual nodes.

        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/9474')
            .elements('css selector', '#main-area div #contentTypeArticle div:nth-child(2) h1', function (result) {
                result.value.map(function (element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function (res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/9474/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });



        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/1210')
            .elements('css selector', '#main-area div #contentTypeArticle div h1', function (result) {
                result.value.map(function (element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function (res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/1210/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });

        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/2488')
            .elements('css selector', '#main-area div #contentTypeArticle div h1', function (result) {
                result.value.map(function (element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function (res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/2488/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });


    }
};
