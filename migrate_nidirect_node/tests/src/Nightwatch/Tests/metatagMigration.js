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
    'Test whether individual custom metatags have been imported (nid 1130)': browser => {
        browser
            .url('https://www.nidirect.gov.uk/node/1130')
            .elements('xpath', "//meta[@name='keywords']", function(result) {
                result.value.map(function(element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'content', function(res) {
                        // Check that the same title appears in D8 after migration.
                        console.log(res.value);
                        browser
                            .drupalRelativeURL('/node/1130')
                            .useXpath()
                            .expect.element("//meta[@name='keywords']")
                            .to.have.attribute('content')
                            .which.equals(res.value);
                    })
                })
            });
    },

    // Look for keywords 'giant hogweed' on node 1826.
    'Test whether individual custom metatags have been imported (nid 1826)': browser => {
        browser
            .url('https://www.nidirect.gov.uk/node/1826')
            .elements('xpath', "//meta[@name='keywords']", function(result) {
                result.value.map(function(element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'content', function(res) {
                        // Check that the same title appears in D8 after migration.
                        console.log(res.value);
                        browser
                            .drupalRelativeURL('/node/1826')
                            .useXpath()
                            .expect.element("//meta[@name='keywords']")
                            .to.have.attribute('content')
                            .which.equals(res.value);
                    })
                })
            });
    },

    // Look for abstract 'v0002' on node 4855.
    'Test whether individual custom metatags have been imported (nid 4855)': browser => {
        browser
            .url('https://www.nidirect.gov.uk/node/4855')
            .elements('xpath', "//meta[@name='abstract']", function(result) {
                result.value.map(function(element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'content', function(res) {
                        // Check that the same title appears in D8 after migration.
                        console.log(res.value);
                        browser
                            .drupalRelativeURL('/node/4855')
                            .useXpath()
                            .expect.element("//meta[@name='abstract']")
                            .to.have.attribute('content')
                            .which.equals(res.value);
                    })
                })
            });
    }


};
