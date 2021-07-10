<template>
  <fragment>
    <img v-if="!asLoggedIn" src="/assets/images/sidenav.jpg" alt="Sidenav">
    <ul v-else class="sidenav-menu">
      <!--
      <router-link :to="{ name: 'home' }" exact class="sidenav-menu-item" tag="li">
        <a>
          <icon class="icon" width="17" height="17" type="dashboard"/>
          <span>Dashboard</span>
        </a>
      </router-link>
      -->
      <router-link
        :to="{ name: 'dashboard' }"
        exact
        class="sidenav-menu-item"
        tag="li"
        v-if="hasHotels && pageAllowed('dashboard')"
      >
        <a :style="currentColor">
          <icon class="icon" width="19" height="19" type="dashboard"/>
          <span>{{ $t('pages.dashboard.title') }}</span>
        </a>
      </router-link>
      <router-link
        to="/reservations"
        class="sidenav-menu-item"
        tag="li"
        v-if="hasHotels && pageAllowed('reservations')"
      >
        <a :style="currentColor">
          <icon class="icon" width="19" height="21" type="reservations"/>
          <span>{{ $t('pages.reservations.title') }}</span>
        </a>
      </router-link>
      <router-link
        to="/calendar"
        tag="li"
        class="sidenav-menu-item"
        v-if="hasHotels && pageAllowed('calendar')"
      >
        <a :style="currentColor">
          <icon class="icon" width="19" height="19" type="calendar"/>
          <span>{{ $t('pages.calendar.title') }}</span>
          <!--
          <icon class="arrow" stroke-width="2" width="12" height="7" type="arrow-down"/>
          -->
        </a>
        <!--
        <ul class="sidenav-menu-dropdown">
          <router-link to="/calendar/quickupdate" class="sidenav-menu-item" tag="li">
            <a>
              <icon class="icon" width="5" height="5" type="dot"/>
              <span>Quick Update</span>
            </a>
          </router-link>
          <router-link to="/calendar/settings" class="sidenav-menu-item" tag="li">
            <a href="#">
              <icon class="icon" width="5" height="5" type="dot"/>
              <span>Settings</span>
            </a>
          </router-link>
        </ul>
        -->
      </router-link>
      <li class="sidenav-menu-item sidenav-menu-item-dropdown"
          :class="{ active: isProductsActive }"
          v-if="hasHotels && pageAllowed('photos', 'roomtypes', 'rateplans', 'mealplans', 'policies')">
        <a @click.prevent="toggleMenu('products')"
           :style="currentColor">
          <icon class="icon" width="19" height="21" type="rateplans"/>
          <span>{{ $t('menu.products') }}</span>
          <icon class="arrow" stroke-width="2" width="12" height="7" type="arrow-down"/>
        </a>
        <ul class="sidenav-menu-dropdown">
          <router-link
            v-if="pageAllowed('roomtypes')"
            to="/room-types" class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isProductsActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isProductsActive == true ?
                  currentBackground : currentColor">{{ $t('pages.roomtypes.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="pageAllowed('rateplans')"
            to="/rate-plans"
            class="sidenav-menu-item"
            tag="li">
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isProductsActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isProductsActive == true ?
                  currentBackground : currentColor">{{ $t('pages.rateplans.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="pageAllowed('mealplans')"
            to="/meal-plans" class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isProductsActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isProductsActive == true ?
                  currentBackground : currentColor">{{ $t('pages.mealplans.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="pageAllowed('photos')"
            to="/photos" class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isProductsActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isProductsActive == true ?
                  currentBackground : currentColor">{{ $t('pages.photos.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="pageAllowed('policies')"
            to="/policies"
            class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isProductsActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isProductsActive == true ?
                  currentBackground : currentColor">{{ $t('pages.policies.title') }}</span>
            </a>
          </router-link>
        </ul>
      </li>
      <li class="sidenav-menu-item sidenav-menu-item-dropdown"
          :class="{ active: isDistributorsActive }"
          v-if="hasHotels && pageAllowed('channels', 'systems')">
        <a
          @click.prevent="toggleMenu('distributors')"
          :style="currentColor">
          <icon class="icon" width="20" height="22" type="connect"/>
          <span>{{ $t('menu.connectivity') }}</span>
          <icon class="arrow" stroke-width="2" width="12" height="7" type="arrow-down"/>
        </a>
        <ul class="sidenav-menu-dropdown">
          <router-link
            v-if="pageAllowed('channels')"
            to="/channels"
            class="sidenav-menu-item"
            tag="li"
            :exact="false"
            active-class="exact-active"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isDistributorsActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isDistributorsActive == true
                  ? currentBackground : currentColor">{{ $t('pages.channels.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="pageAllowed('systems')"
            to="/systems"
            class="sidenav-menu-item"
            tag="li"
            :exact="false"
            active-class="exact-active"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isDistributorsActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isDistributorsActive == true
                  ? currentBackground : currentColor">{{ $t('pages.systems.title') }}</span>
            </a>
          </router-link>
        </ul>
      </li>
      <li class="sidenav-menu-item sidenav-menu-item-dropdown" :class="{ active: isPropertyActive }"
          v-if="hasHotels &&
            pageAllowed('facilities', 'nearby', 'contactpersons', 'description', 'booking', 'masterdata')">
        <a
          @click.prevent="toggleMenu('property')"
          :style="currentColor">
          <icon class="icon" width="19" height="21" type="home"/>
          <span>{{ $t('menu.property') }}</span>
          <icon class="arrow" stroke-width="2" width="12" height="7" type="arrow-down"/>
        </a>
        <ul class="sidenav-menu-dropdown">
          <router-link
            v-if="pageAllowed('masterdata')"
            to="/master-data"
            class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isPropertyActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isPropertyActive == true
                  ? currentBackground : currentColor">{{ $t('pages.masterdata.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="pageAllowed('booking')"
            to="/booking-status"
            class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isPropertyActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isPropertyActive == true
                  ? currentBackground : currentColor">{{ $t('pages.booking.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="pageAllowed('description')"
            to="/descriptions"
            class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isPropertyActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isPropertyActive == true
                  ? currentBackground : currentColor">{{ $t('pages.description.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="pageAllowed('contactpersons')"
            to="/contact-persons"
            class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isPropertyActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isPropertyActive == true
                  ? currentBackground : currentColor">{{ $t('pages.contactpersons.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="pageAllowed('nearby')"
            to="/nearby"
            class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isPropertyActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isPropertyActive == true
                  ? currentBackground : currentColor">{{ $t('pages.nearby.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="pageAllowed('facilities')"
            to="/facilities"
            class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isPropertyActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isPropertyActive == true
                  ? currentBackground : currentColor">{{ $t('pages.facilities.title') }}</span>
            </a>
          </router-link>
        </ul>
      </li>
      <li class="sidenav-menu-item sidenav-menu-item-dropdown" :class="{ active: isManageActive }"
          v-if="userPageAllowed('group', 'hotels', 'users')">
        <a :style="currentColor"
           @click.prevent="toggleMenu('manage')">
          <icon class="icon" width="22" height="22" type="settings"/>
          <span>{{ $t('menu.management') }}</span>
          <icon class="arrow" stroke-width="2" width="12" height="7" type="arrow-down"/>
        </a>
        <ul class="sidenav-menu-dropdown">
          <router-link
            v-if="userPageAllowed('group')"
            to="/group"
            class="sidenav-menu-item"
            tag="li"
          >
            <a
              :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isGroupActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isGroupActive == true
                  ? currentBackground : currentColor">{{ $t('pages.group.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="userPageAllowed('hotels')"
            to="/hotels"
            class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isHotelsActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isHotelsActive == true
                  ? currentBackground : currentColor">{{ $t('pages.hotels.title') }}</span>
            </a>
          </router-link>
          <router-link
            v-if="userPageAllowed('users')"
            to="/users"
            class="sidenav-menu-item"
            tag="li"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="!isAdmin && isUsersActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isUsersActive == true
                  ? currentBackground : currentColor">{{ $t('pages.users.title') }}</span>
            </a>
          </router-link>
        </ul>
      </li>
      <hr v-if="isAdmin" />
      <li class="sidenav-menu-item sidenav-menu-item-dropdown" :class="{ active: isAdminActive }"
          v-if="isAdmin">
        <a
          @click.prevent="toggleMenu('admin')"
          :style="currentColor"
        >
          <icon class="icon" width="24" height="24" type="admin"/>
          <span>{{ $t('menu.admin') }}</span>
          <icon class="arrow" stroke-width="2" width="12" height="7" type="arrow-down"/>
        </a>
        <ul class="sidenav-menu-dropdown">
          <router-link
            to="/admin/groups"
            class="sidenav-menu-item"
            tag="li"
            :exact="false"
            active-class="exact-active"
          >
            <a :style="currentBackground">
              <icon class="icon" width="5" height="5" type="dot"
                    :style="isAdminProperityActive == true
                      ? currentBackground : currentColor"/>
              <span
                :style="isAdminProperityActive == true ?
                  currentBackground : currentColor">{{ $t('pages.groups.title') }}</span>
            </a>
          </router-link>
        </ul>
      </li>
      <!--
      <li class="sidenav-menu-item">
        <a href="#">
          <icon class="icon" width="19" height="19" type="color-customization"/>
          <span>Color customization</span>
        </a>
      </li>
      <li class="sidenav-menu-item">
        <a href="#">
          <icon class="icon" width="20" height="19" type="email-customization"/>
          <span>Email customization</span>
        </a>
      </li>
      <li class="sidenav-menu-item">
        <a href="#">
          <icon class="icon" width="12" height="15" type="text-customization"/>
          <span>Text customization</span>
        </a>
      </li>
      <li class="sidenav-menu-item">
        <a href="#">
          <icon class="icon" width="20" height="16" type="translation-tool"/>
          <span>Translation tool</span>
        </a>
      </li>
      <li class="sidenav-menu-item">
        <a href="#">
          <icon class="icon" width="22" height="22" type="blank-circle"/>
          <span>Payment provider</span>
        </a>
      </li>
      <li class="sidenav-menu-item">
        <a href="#">
          <icon class="icon" width="22" height="22" type="blank-circle"/>
          <span>Picture administation</span>
        </a>
      </li>
      <li class="sidenav-menu-item">
        <a href="#">
          <icon class="icon" width="22" height="22" type="blank-circle"/>
          <span>File administration</span>
        </a>
      </li>
      <li class="sidenav-menu-item">
        <a href="#">
          <icon class="icon" width="22" height="22" type="blank-circle"/>
          <span>Property details</span>
        </a>
      </li>
      <li
        class="sidenav-menu-item sidenav-menu-item-dropdown"
        :class="{
          open: expandRooms
        }"
      >
        <a
          href="#"
          @click="expandRooms = !expandRooms"
        >
          <icon class="icon" width="22" height="22" type="blank-circle"/>
          <span>Rooms & Rates</span>
          <icon class="arrow" stroke-width="2" width="12" height="7" type="arrow-down"/>
        </a>
        <ul class="sidenav-menu-dropdown">
          <li class="sidenav-menu-item">
            <a href="#">
              <icon class="icon" width="5" height="5" type="dot"/>
              <span>Quick Update</span>
            </a>
          </li>
          <li class="sidenav-menu-item">
            <a href="#">
              <icon class="icon" width="5" height="5" type="dot"/>
              <span>Settings</span>
            </a>
          </li>
        </ul>
      </li>
      <li class="sidenav-menu-item">
        <a href="#">
          <icon class="icon" width="22" height="22" type="blank-circle"/>
          <span>Invoice archive</span>
        </a>
      </li>
      <li class="sidenav-menu-item">
        <a href="#">
          <icon class="icon" width="22" height="22" type="blank-circle"/>
          <span>Logging</span>
        </a>
      </li>
      <li class="sidenav-menu-item sidenav-menu-item-dropdown"
          :class="{ open: expandUA }"
      >
        <a
          href="#"
          @click="expandUA = !expandUA"
        >
          <icon class="icon" width="23" height="15" type="user-group"/>
          <span>Users & Access</span>
          <icon class="arrow" stroke-width="2" width="12" height="7" type="arrow-down"/>
        </a>
        <ul class="sidenav-menu-dropdown">
          <li class="sidenav-menu-item">
            <a href="#">
              <icon class="icon" width="5" height="5" type="dot"/>
              <span>Quick Update</span>
            </a>
          </li>
          <li class="sidenav-menu-item">
            <a href="#">
              <icon class="icon" width="5" height="5" type="dot"/>
              <span>Settings</span>
            </a>
          </li>
        </ul>
      </li>
      -->
    </ul>
  </fragment>
</template>

<script>
  import { mapGetters } from 'vuex';

  export default {
    name: 'SideNav',
    data: () => ({
      menu: {
        products: false,
        property: false,
        distributors: false,
        manage: false,
        admin: false,
        active: '',
      },
    }),
    computed: {
      ...mapGetters('user', ['loggedIn', 'hasHotels', 'pageAllowed', 'userPageAllowed', 'currentColorFont', 'isAdmin', 'currentColorSchema']),
      ...mapGetters(['centered']),
      currentColor() {
        const {
          r, g, b, a,
        } = this.currentColorFont.rgba;
        return `color: rgba(${r}, ${g}, ${b}, ${a}) !important`;
      },
      currentBackground() {
        const {
          r, g, b, a,
        } = this.currentColorSchema.rgba;
        return `color: rgba(${r}, ${g}, ${b}, ${a})`;
      },
      asLoggedIn() {
        return this.loggedIn && !this.centered;
      },
      isProductsActive() {
        return this.menu.products
          || ['rateplans', 'roomtypes', 'policies', 'photos', 'mealplans'].includes(this.$route.name);
      },
      isPropertyActive() {
        return this.menu.property
          || ['masterdata', 'contactpersons', 'facilities', 'nearby', 'booking', 'description'].includes(this.$route.name);
      },
      isDistributorsActive() {
        return this.menu.distributors || ['channels', 'channel', 'systems'].includes(this.$route.name);
      },
      isGroupActive() {
        return this.menu.manage || ['group'].includes(this.$route.name);
      },
      isHotelsActive() {
        return this.menu.manage || ['hotels'].includes(this.$route.name);
      },
      isUsersActive() {
        return this.menu.manage || ['users'].includes(this.$route.name);
      },
      isManageActive() {
        return this.menu.manage || ['group', 'hotels', 'users'].includes(this.$route.name);
      },
      isAdminProperityActive() {
        const { name } = this.$route;
        return this.menu.admin || ['admin-groups'].includes(name);
      },
      isAdminActive() {
        const { name } = this.$route;
        return this.menu.admin || (name != null && name.indexOf('admin-') === 0);
      },
    },
    watch: {
      $route() {
        this.toggleMenu();
      },
    },
    methods: {
      toggleMenu(name = null) {
        // const element = document.querySelector('.exact-active a');
        // if (element) {
        //   element.style.color = '#ccc';
        // } else {
        //   const ele = document.querySelector('.sidenav-menu-item a');
        //   ele.style.color = '#000 !important';
        //   console.log('ele.style.color', ele.style.color);
        // }
        Object.keys(this.menu).forEach((k) => {
          this.menu[k] = k === name ? !this.menu[k] : false;
        });
      },
    },
  };
</script>
