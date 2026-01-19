import { defineStore } from "pinia";
import { api } from "../lib/api";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,       // { id, email, name, role } or null
    loading: false,
    error: "",  // 상태메시지 남길 때
    bootstrapped: false, // 앱 시작 시 me 체크 완료 여부
  }),

  getters: {
    isLoggedIn: (s) => !!s.user,
    isAdmin: (s) => s.user?.role === "admin",
  },

  actions: {
    async fetchMe() {
      this.loading = true;
      this.error = "";
      try {
        const res = await api.get("/auth/me.php");
        this.user = res.data?.data ?? null;
        console.log('로그인 완료');
      } catch (e) {
        this.user = null;
        console.log('로그인 필요');
      } finally {
        this.loading = false;
        this.bootstrapped = true;
      }
    },

    async login(email, password) {
      this.loading = true;
      this.error = "";
      try {
        await api.post("/auth/login.php", { email, password });
        await this.fetchMe(); // 세션 반영 확인
      } catch (e) {
        this.user = null;
        this.error =
          e?.response?.data?.error?.message ||
          "로그인 실패 (계정/서버/CORS 확인)";
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      this.loading = true;
      this.error = "";
      try {
        await api.post("/auth/logout.php");
        this.user = null;
      } catch (e) {
        this.error = "로그아웃 실패";
      } finally {
        this.loading = false;
      }
    },

    async register(payload) {
      this.loading = true;
      this.error = "";
      try {
        const res = await api.post("/auth/register.php", payload);
        this.user = res.data.data;
        this.bootstrapped = true;
      } catch (e) {
        this.error = e?.response?.data?.message || "회원가입 실패";
        throw e;
      } finally {
        this.loading = false;
      }
    }
  },
});
