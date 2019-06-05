module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether Metatag tokens exist on Articles': browser => {
        browser
            .drupalLogin({name: 'admin', password: 'letmein'});

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-meta-tags').to.be.present;
    },

    'Test whether Metatag global config has been imported': browser => {
        browser
            .drupalRelativeURL('/admin/config/search/metatag')
            .useXpath()
            .expect.element('//td[text()="[node:field-summary]"]').to.be.present;
    },

    // Look for keyword 'exercise' on node 1130.
    'Test whether individual custom metatags have been imported': browser => {
        browser
            .url('https://www.nidirect.gov.uk/node/1130')
            .elements('xpath', "//meta[@name=\"keywords\"]", function(result) {
                result.value.map(function(element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'content', function(res) {
                        // Check that the same title appears in D8 after migration.
                        console.log(res.value);
                        browser
                            .drupalRelativeURL('/node/1130')
                            .useXpath()
                            .expect.element("//meta[@name=\"keywords\"]")
                            .to.have.attribute('content')
                            .which.equals(res.value);
                    })
                })
            });
    }


};
