import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import ResetPassword from '../views/ResetPassword.vue'
import Seminare from '../views/Seminare.vue'
import store from '../store'

Vue.use(VueRouter)

const ifNotAuthenticated = (to, from, next) => {
  if (!store.getters.isAuthenticated) {
    next()
    return
  }
  next('/')
}

const ifAuthenticated = (to, from, next) => {
  if (store.getters.isAuthenticated) {
    next()
    return
  }
  next('/login')
}

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    beforeEnter: ifAuthenticated
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    beforeEnter: ifNotAuthenticated
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    beforeEnter: ifAuthenticated
  },
  {
    path: '/reset_password',
    name: 'ResetPassword',
    component: ResetPassword,
    beforeEnter: ifAuthenticated
  },
  {
    path: '/events',
    name: 'Seminare',
    component: Seminare,
    beforeEnter: ifAuthenticated
  },
  {
    path: '/queries',
    name: 'Queries',
    component: Home,
    beforeEnter: ifAuthenticated
  },
  {
    path: '/forms',
    name: 'Forms',
    component: Home,
    beforeEnter: ifAuthenticated
  },
  {
    path: '/delete-data',
    name: 'Delete-Data',
    component: Home,
    beforeEnter: ifAuthenticated
  },
  {
    path: '*',
    redirect: '/',
    beforeEnter: ifAuthenticated
  }
]

const router = new VueRouter({
  mode: 'history',
  routes: routes,
  linkExactActiveClass: 'active'
})

export default router
