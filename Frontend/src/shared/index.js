export const appEndpoint = process.env.VUE_APP_ENDPOINT_URL;
export const apiEndpoint = process.env.VUE_APP_API_ENDPOINT_URL;
export const cookieEndpoint = process.env.VUE_APP_COOKIE_ENDPOINT_URL;

export const externalEngineURL = process.env.VUE_APP_EXTERNAL_ENGINE_URL;
export const creditCardDetailsIframe = process.env.VUE_APP_CREDIT_CARD_DETAILS;

export const quMainFields = [
  'avail',
  'price',
  'osale',
];

export const quMoreFields = [
  'minlos',
  'maxlos',
  'carr',
  'cdep',
  'grnt',
];

export const distanceUnits = ['km', 'm', 'ft', 'mi'];

export const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

export const locales = [
  { code: 'en', title: 'English' },
  { code: 'de', title: 'Deutsch' },
  { code: 'tr', title: 'Türkçe' },
  // { code: 'ru', title: 'Русский' },
];
export const langCodes = [
  'en', 'de', 'tr', 'fr', 'ru', 'zh',
  'pt', 'it', 'es', 'pl', 'nl', 'ro',
];

export const timeUnits = ['hour', 'day', 'week', 'month', 'year'];
export const dropTimes = ['BeforeArrival', 'AfterBooking', 'AfterConfirmation'];
export const basisTypes = ['FullStay', 'Nights'];
export const channelUpdateTypes = [
  { id: 0, text: 'channel-update-types.0' },
  { id: 1, text: 'channel-update-types.1' },
  { id: 2, text: 'channel-update-types.2' },
  { id: 3, text: 'channel-update-types.3' },
];

export const bookable = {
  anytime: 0,
  periods: 1,
  fromto: 2,
  until: 3,
  within: 4,
};

export const legalPages = [
  { id: 6, slug: 'Legal Notice'.slugify() },
  { id: 4, slug: 'Terms & Conditions'.slugify() },
  { id: 11, slug: 'Privacy Policy'.slugify() },
  { id: 9, slug: 'Data Processing Agreement'.slugify() },
  { id: 10, slug: 'Data Processors'.slugify() },
];

export const defaultTextColors = {
  black: {
    rgba: {
      r: '0',
      g: '0',
      b: '0',
      a: '1',
    },
  },
  white: {
    rgba: {
      r: '255',
      g: '255',
      b: '255',
      a: '1',
    },
  },
};

export const defaultBackgroundColor = {
  rgba: {
    r: '74',
    g: '144',
    b: '226',
    a: '1',
  },
};

export default {
  appEndpoint,
  apiEndpoint,
  cookieEndpoint,
  externalEngineURL,

  quMainFields,
  quMoreFields,

  locales,
  weekdays,
  timeUnits,
  dropTimes,
  basisTypes,
  bookable,

  defaultTextColors,
  defaultBackgroundColor,
  creditCardDetailsIframe,
};
