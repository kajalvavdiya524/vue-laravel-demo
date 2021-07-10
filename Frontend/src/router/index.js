import Vue from 'vue';
import VueRouter from 'vue-router';
import StorageService from '@/services/storage.service';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'home',
  },
  {
    path: '/no-perms',
    name: 'noperms',
    component: () => import(/* webpackChunkName: "user" */ '@/views/User/NoPermissions'),
    meta: {
      // centered: true,
      sideBar: false,
      empty: true,
    },
  },
  {
    path: '/login',
    name: 'login',
    meta: {
      title: 'Welcome',
      documentTitle: '*pages.dashboard.title',
      public: true,
      guestOnly: true,
    },
    component: () => import(/* webpackChunkName: "auth" */ '@/views/Auth/Login.vue'),
  },
  {
    path: '/register',
    name: 'register',
    meta: {
      title: 'Sign Up',
      documentTitle: '*signup.title',
      sideBar: false,
      public: true,
      guestOnly: true,
    },
    component: () => import(/* webpackChunkName: "auth" */ '@/views/Auth/Register.vue'),
  },
  {
    path: '/password/email',
    name: 'password.email',
    meta: {
      title: 'Forgot Password',
      documentTitle: '*forgot-pwd.title',
      public: true,
      guestOnly: true,
    },
    component: () => import(/* webpackChunkName: "auth" */ '@/views/Auth/ForgotPassword.vue'),
  },
  {
    path: '/password/change',
    name: 'password.change',
    meta: {
      title: 'Change Password',
      documentTitle: '*change-pwd.title',
      public: true,
      guestOnly: true,
    },
    component: () => import(/* webpackChunkName: "auth" */ '@/views/Auth/ChangePasswordSent.vue'),
  },
  {
    path: '/email/change',
    name: 'email.change',
    meta: {
      title: 'Change Email',
      documentTitle: '*change-email.title',
      public: true,
      guestOnly: true,
    },
    component: () => import(/* webpackChunkName: "auth" */ '@/views/Auth/ChangeEmailRequestSent.vue'),
  },
  {
    path: '/email/update',
    name: 'email.update',
    meta: {
      title: 'Change Email',
      documentTitle: '*change-email.title',
      public: true,
      guestOnly: true,
    },
    component: () => import(/* webpackChunkName: "auth" */ '@/views/Auth/ChangeEmail.vue'),
  },
  {
    path: '/password/reset',
    name: 'password.reset',
    meta: {
      title: 'Reset Password',
      documentTitle: '*reset-pwd.title',
      public: true,
      guestOnly: true,
    },
    component: () => import(/* webpackChunkName: "auth" */ '@/views/Auth/ResetPassword.vue'),
  },
  {
    path: '/email/verify/:id?/:hash?',
    name: 'verification.notice',
    meta: {
      sideBar: false,
      title: 'Verification',
      documentTitle: '*signup.verify.title',
      centered: true,
    },
    component: () => import(/* webpackChunkName: "user" */ '@/views/User/VerifyEmailNotice.vue'),
  },
  {
    path: '/details',
    name: 'details',
    meta: {
      centered: true,
    },
    component: () => import(/* webpackChunkName: "user" */ '@/views/User/Details.vue'),
  },
  {
    path: '/setup',
    name: 'setup',
    meta: {
      title: 'Setup Wizard',
      stretch: true,
    },
    component: () => import(/* webpackChunkName: "user" */ '@/views/User/Setup.vue'),
  },
  {
    path: '/calendar',
    name: 'calendar',
    meta: {
      title: 'Calendar',
      documentTitle: '*pages.calendar.title',
    },
    component: () => import(/* webpackChunkName: "calendar" */ '@/views/Calendar.vue'),
  },
  {
    path: '/invoices',
    name: 'invoices',
    meta: {
      title: 'Invoices',
      documentTitle: '*pages.invoices.title',
    },
    component: () => import(/* webpackChunkName: "invoices" */ '@/views/Invoices.vue'),
  },
  {
    path: '/profile',
    name: 'profile',
    meta: {
      title: 'Profile Settings',
      documentTitle: '*pages.profile.title',
      empty: true,
    },
    component: () => import(/* webpackChunkName: "profile" */ '@/views/User/Profile.vue'),
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    meta: {
      title: 'Dashboard',
      documentTitle: '*pages.dashboard.title',
    },
    component: () => import(/* webpackChunkName: "dashboard" */ '@/views/Dashboard.vue'),
  },
  {
    path: '/reservations',
    name: 'reservations',
    meta: {
      title: 'Reservations',
      documentTitle: '*pages.reservations.title',
    },
    component: () => import(/* webpackChunkName: "reservations" */ '@/views/Reservations.vue'),
  },
  {
    path: '/legal/:page?',
    name: 'legal',
    meta: {},
    component: () => import(/* webpackChunkName: "legal" */ '@/views/Legal.vue'),
  },
  {
    path: '/users',
    name: 'users',
    meta: {
      title: 'Users & Roles',
      documentTitle: '*pages.users.title',
      empty: true,
    },
    component: () => import(/* webpackChunkName: "users" */ '@/views/Users.vue'),
  },
  {
    path: '/meal-plans',
    name: 'mealplans',
    meta: {
      title: 'Meal plans',
      documentTitle: '*pages.mealplans.title',
    },
    component: () => import(/* webpackChunkName: "mealplans" */ '@/views/MealPlans.vue'),
  },
  {
    path: '/room-types',
    name: 'roomtypes',
    meta: {
      title: 'Room types',
      documentTitle: '*pages.roomtypes.title',
    },
    component: () => import(/* webpackChunkName: "roomtypes" */ '@/views/RoomTypes.vue'),
  },
  {
    path: '/rate-plans',
    name: 'rateplans',
    meta: {
      title: 'Rate plans',
      documentTitle: '*pages.rateplans.title',
    },
    component: () => import(/* webpackChunkName: "rateplans" */ '@/views/RatePlans.vue'),
  },
  {
    path: '/master-data',
    name: 'masterdata',
    meta: {
      title: 'Master Data',
      documentTitle: '*pages.masterdata.title',
    },
    component: () => import(/* webpackChunkName: "masterdata" */ '@/views/MasterData.vue'),
  },
  {
    path: '/booking-status',
    name: 'booking',
    meta: {
      title: 'Booking Status',
      documentTitle: '*pages.booking.title',
    },
    component: () => import(/* webpackChunkName: "booking" */ '@/views/BookingStatus.vue'),
  },
  {
    path: '/contact-persons',
    name: 'contactpersons',
    meta: {
      title: 'Contact Persons',
      documentTitle: '*pages.contactpersons.title',
    },
    component: () => import(/* webpackChunkName: "contactpersons" */ '@/views/ContactPersons.vue'),
  },
  {
    path: '/nearby',
    name: 'nearby',
    meta: {
      title: 'What\'s nearby',
      documentTitle: '*pages.nearby.title',
    },
    component: () => import(/* webpackChunkName: "nearby" */ '@/views/NearBy.vue'),
  },
  {
    path: '/facilities',
    name: 'facilities',
    meta: {
      title: 'Facilities',
      documentTitle: '*pages.facilities.title',
    },
    component: () => import(/* webpackChunkName: "facilities" */ '@/views/Facilities.vue'),
  },
  {
    path: '/policies',
    name: 'policies',
    meta: {
      title: 'Policies',
      documentTitle: '*pages.policies.title',
    },
    component: () => import(/* webpackChunkName: "policies" */ '@/views/Policies.vue'),
  },
  {
    path: '/photos',
    name: 'photos',
    meta: {
      title: 'Photos',
      documentTitle: '*pages.photos.title',
    },
    component: () => import(/* webpackChunkName: "photos" */ '@/views/Photos.vue'),
  },
  {
    path: '/channels',
    name: 'channels',
    meta: {
      title: 'Channels',
      documentTitle: '*pages.channels.title',
    },
    component: () => import(/* webpackChunkName: "channels" */ '@/views/Channels.vue'),
  },
  {
    path: '/channels/:id',
    name: 'channel',
    meta: {
      permission: 'channels',
      title: 'Channels',
      documentTitle: '*pages.channels.title',
    },
    component: () => import(/* webpackChunkName: "channels" */ '@/views/Channel.vue'),
  },
  {
    path: '/systems',
    name: 'systems',
    meta: {
      title: 'Systems',
      documentTitle: '*pages.systems.title',
    },
    component: () => import(/* webpackChunkName: "systems" */ '@/views/Systems.vue'),
  },
  {
    path: '/hotels',
    name: 'hotels',
    meta: {
      title: 'Properties',
      documentTitle: '*pages.hotels.title',
      empty: true,
    },
    component: () => import(/* webpackChunkName: "hotels" */ '@/views/Hotels.vue'),
  },
  {
    path: '/group',
    name: 'group',
    meta: {
      title: 'Property Group',
      documentTitle: '*pages.group.title',
      empty: true,
    },
    component: () => import(/* webpackChunkName: "group" */ '@/views/Group.vue'),
  },
  {
    path: '/descriptions',
    name: 'description',
    meta: {
      title: 'Descriptions',
      documentTitle: '*pages.description.title',
    },
    component: () => import(/* webpackChunkName: "descriptions" */ '@/views/Descriptions.vue'),
  },
  {
    path: '/admin/groups',
    name: 'admin-groups',
    meta: {
      title: 'Groups',
      documentTitle: '*pages.groups.title',
      admin: true,
      empty: true,
    },
    component: () => import(/* webpackChunkName: "admin" */ '@/views/Admin/Groups.vue'),
  },
  {
    path: '/admin/groups/:id?',
    name: 'admin-group',
    meta: {
      title: '*pages.groups.manage.title',
      admin: true,
      empty: true,
    },
    component: () => import(/* webpackChunkName: "admin" */ '@/views/Admin/Group.vue'),
  },
];

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  linkExactActiveClass: 'exact-active',
  linkActiveClass: 'active',
  routes,
});

export default router;

export { router };

let initialUserCheckDone = false;

let $store = null;
const checkLoggedIn = () => new Promise((resolve) => {
  Vue.nextTick(async () => {
    // save $store for future use
    $store = router.app.$store;
    await $store.dispatch('user/getUser');
    resolve();
  });
});

const updateMeta = (store, to) => {
  const {
    documentTitle, sideBar, centered, stretch,
  } = to.meta;
  store.commit('pageTitle', documentTitle);
  store.commit('sidebar', sideBar);
  store.commit('centered', centered);
  store.commit('stretch', stretch);
};

const initialPageByPerms = () => {
  const perms = $store.getters['user/allowedUserPages'];
  if (perms.includes('hotels')) return 'hotels';
  if (perms.includes('users')) return 'users';
  if (perms.includes('group')) return 'group';
  return 'noperms';
};

const initialPageForHotel = () => {
  const perms = $store.getters['user/allowedPages'];
  if (!perms.length) return 'noperms';
  if (perms.includes('calendar')) return 'calendar';
  return perms[0];
};

const checkPageAccess = (route) => {
  const { meta, name } = route;
  const n = meta != null ? (meta.permission || name) : name;
  const h = $store.getters['user/allowedPages'];
  const u = $store.getters['user/allowedUserPages'];
  return h.includes(n) || u.includes(n);
};

const getInitialRoute = () => {
  const isAdmin = $store.getters['user/isAdmin'];
  if (isAdmin) return 'admin-groups';
  const hasHotels = $store.getters['user/hasHotels'];
  return hasHotels ? initialPageForHotel() : initialPageByPerms();
};

router.pushInitial = () => {
  const name = getInitialRoute();
  router.push({ name });
};

router.beforeEach(async (to, from, next) => {
  // eslint-disable-next-line no-underscore-dangle
  const _next = next;
  // eslint-disable-next-line no-param-reassign
  next = function newnext(loc) {
    return _next(loc);
  };
  if (!initialUserCheckDone) {
    await checkLoggedIn();
    initialUserCheckDone = true;
  }

  // clear server-validation states in store modules
  $store.dispatch('clearErrors');

  // const {
  //   title, sideBar, centered, stretch,
  // } = to.meta;
  // $store.commit('pageTitle', title);
  // $store.commit('sidebar', sideBar);
  // $store.commit('centered', centered);
  // $store.commit('stretch', stretch);

  // By default all routes are protected for users only
  const isGuestOnly = to.matched.some((r) => r.meta.guestOnly);
  const isPublic = to.matched.some((r) => r.meta.public);
  const isForAdmin = to.matched.some((r) => r.meta.admin === true);
  const forEmptyHotels = to.matched.some((r) => r.meta.empty === true);
  const skipPerms = to.matched.some((r) => r.meta.perms === false);
  const isLoggedIn = !!StorageService.getUser();

  const hasHotels = $store.getters['user/hasHotels'];
  const isAdmin = $store.getters['user/isAdmin'];

  // prevent guests from visiting logged-in pages
  if (!isPublic && !isLoggedIn) {
    const redirect = to.fullPath;
    const toLogin = { name: 'login' };
    if (redirect !== '/') {
      toLogin.query = { redirect };
    }
    return next(toLogin);
  }

  if (to.name === 'home') {
    return next({ name: getInitialRoute() });
  }

  if (!isLoggedIn) {
    updateMeta($store, to);
    return next();
  }

  const initial = hasHotels ? initialPageForHotel() : initialPageByPerms();

  // prevent logged-in users from visiting guests-only pages
  if (isLoggedIn && isGuestOnly) {
    return next({ name: initial });
  }

  // check conditions that prevents users from continue
  // until they fill some information or make some actions
  // ---
  // initial check has been done already, we can use $store here
  // const { $store } = router.app;

  // check for verified email
  if (!$store.getters['user/emailVerified']) {
    if (from.name === 'verification.notice') {
      return next(false);
    }
    if (to.name !== 'verification.notice') {
      return next({ name: 'verification.notice' });
    }
    updateMeta($store, to);
    return next();
  }
  if (to.name === 'verification.notice') {
    return next({ name: initial });
  }
  // ***

  // check for filled information
  if (!$store.getters['user/requiredFilled']) {
    if (from.name === 'details') {
      return next(false);
    }
    if (to.name !== 'details') {
      return next({ name: 'details' });
    }
    updateMeta($store, to);
    return next();
  }
  if (to.name === 'details') {
    return next({ name: initial });
  }
  // // ***

  // check for setup
  if (!$store.getters['user/setupComplete']) {
    if (from.name === 'setup') {
      return next(false);
    }
    if (to.name !== 'setup') {
      return next({ name: 'setup' });
    }
    updateMeta($store, to);
    return next();
  }
  if (to.name === 'setup') {
    return next({ name: initial });
  }
  // // ***

  // check for admin rights
  if (isForAdmin && !isAdmin) {
    return next({ name: initial });
  }

  // check for no hotels
  if (!hasHotels && !forEmptyHotels) {
    return next({ name: initial });
  }

  // check for permissions
  if (!isAdmin && !checkPageAccess(to) && !skipPerms && to.name !== 'noperms') {
    return next({ name: initial });
  }

  updateMeta($store, to);
  return next();
});

// router.beforeResolve((to, from, next) => {
//   const { title, sideBar, centered } = to.meta;
//   // const { $store } = router.app;
//   $store.commit('pageTitle', title);
//   $store.commit('sidebar', sideBar);
//   $store.commit('centered', centered);
//   next();
// });
