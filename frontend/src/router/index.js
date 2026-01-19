import { createRouter, createWebHistory } from "vue-router";
import Login from "../pages/Login.vue";
import Admin from "../pages/Admin.vue";
import Posts from "../pages/Posts.vue";
import PostDetail from "../pages/PostDetail.vue";
import PostCreate from "../pages/PostCreate.vue";
import Register from "../pages/Register.vue";

import { useAuthStore } from "../stores/auth";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: "/", redirect: "/login" },
    { path: "/login", component: Login, meta: { public: true } },
    { path: "/register", component: Register, meta: { public: true } },
    { path: "/posts", component: Posts, meta: { requiresAuth: true }},
    { path: "/posts/:id", name: 'Post', component: PostDetail, meta: { requiresAuth: true }},
    { path: "/posts/new", component: PostCreate, meta: { requiresAuth: true }},
    { path: "/admin", component: Admin, meta: { requiresAdmin: true } },
  ],
});

// 라우트 진입 전에 로그인 상태 확인 + 권한 체크
router.beforeEach(async (to) => {
  const auth = useAuthStore();

  // 앱 시작 시 1회 me 호출 (새로고침 대비)
  if (!auth.bootstrapped) {
    await auth.fetchMe();
  }

  // /login 은 누구나 접근 가능
  if (to.meta.public) {
    if (to.path === "/login" && auth.isLoggedIn) {
      return "/posts";
    }
    return true;
  }

  if (to.meta.requiresAuth && !auth.isLoggedIn) return "/login";

  // admin 보호
  if (to.meta.requiresAdmin) {
    if (!auth.isLoggedIn) return "/login";
    if (!auth.isAdmin) return "/posts";
  }

  return true;
});

export default router;
