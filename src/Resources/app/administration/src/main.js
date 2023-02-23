import './service/apiBridgeTestService';
import './service/apiUpdateKeyService';
import './component/api-bridge-test-button';
import './component/api-key-update-button';

import localeDE from './snippet/de_DE.json';
import localeEN from './snippet/en_GB.json';
Shopware.Locale.extend('de-DE', localeDE);
Shopware.Locale.extend('en-GB', localeEN);