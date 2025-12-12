import {
  createStore,
  MutationTree,
  ActionContext,
  ActionTree,
  GetterTree,
  Store as VuexStore,
  CommitOptions,
  DispatchOptions,
  createLogger
} from "vuex";
import axios from 'axios';

//declare state
export type State = {
  progressBar: boolean;
  accessToken: string;
  permissionList: string;
  currency: string;
  title: string;
  dialogState: boolean;
  receiptID: string;
  visitDate: string;
  totalBill: number;
};

//set state
const state: State = {
  progressBar: false,
  accessToken: localStorage.getItem('store_token') || '',
  permissionList: localStorage.getItem('permission_list') || '',
  title: '',
  currency: localStorage.getItem('currency') || '',
  dialogState: false,
  receiptID: '',
  visitDate: '',
  totalBill: 0
};

// mutations and action enums
export enum MutationTypes {
  PROGRESS_BAR           = "PROGRESS_BAR",
  AUTH_REQUEST           = "AUTH_REQUEST",
  PERMISSION_LIST        = "PERMISSION_LIST",
  AUTH_LOGOUT            = "AUTH_LOGOUT",
  OPEN_DIALOG            = "OPEN_DIALOG",
  GET_RECEIPT_TITLE      = "GET_RECEIPT_TITLE",
  GET_CURRENCY           = "GET_CURRENCY",
  GET_RECEIPT_ID         = "GET_RECEIPT_ID",
  GET_VISIT_DATE         = "GET_VISIT_DATE",
  TOTAL_BILL             = "TOTAL_BILL",
}

export enum ActionTypes {
  PROGRESS_BAR        = "PROGRESS_BAR",
  AUTH_REQUEST        = "AUTH_REQUEST",
  PERMISSION_LIST     = "PERMISSION_LIST",
  AUTH_LOGOUT         = "AUTH_LOGOUT",
  OPEN_DIALOG         = "OPEN_DIALOG",
  GET_RECEIPT_ID      = "GET_RECEIPT_ID",
  GET_VISIT_DATE      = "GET_VISIT_DATE",
  GET_RECEIPT_TITLE   = "GET_RECEIPT_TITLE",
  GET_CURRENCY        = "GET_CURRENCY",
  TOTAL_BILL          = "TOTAL_BILL",
}

//Mutation Types
export type Mutations<S = State> = {
  [MutationTypes.PROGRESS_BAR](state: S, payload: boolean): void;
  [MutationTypes.AUTH_REQUEST](state: S, payload: string): void;
  [MutationTypes.PERMISSION_LIST](state: S, payload: string): void;
  [MutationTypes.AUTH_LOGOUT](state: S, payload: string): void;
  [MutationTypes.OPEN_DIALOG](state: S, payload: boolean): void;
  [MutationTypes.GET_RECEIPT_ID](state: S, payload: string): void;
  [MutationTypes.GET_VISIT_DATE](state: S, payload: string): void;
  [MutationTypes.GET_RECEIPT_TITLE](state: S, payload: string): void;
  [MutationTypes.GET_CURRENCY](state: S, payload: string): void;
  [MutationTypes.TOTAL_BILL](state: S, payload: number): void;
};

//define mutations
const mutations: MutationTree<State> & Mutations = {
  [MutationTypes.PROGRESS_BAR](state: State, payload: boolean) {
    state.progressBar = payload;
  },
  [MutationTypes.AUTH_REQUEST](state: State, token: string) {
    state.accessToken = token;
  },
  [MutationTypes.PERMISSION_LIST](state: State, permissions: string) {
    state.permissionList = permissions;
  },
  [MutationTypes.AUTH_LOGOUT](state: State, token: string) {
    state.accessToken = token;
  },
  [MutationTypes.OPEN_DIALOG](state: State, payload: boolean) {
    state.dialogState = payload;
  },
  [MutationTypes.GET_RECEIPT_ID](state: State, payload: string) {
    state.receiptID = payload;
  },
  [MutationTypes.GET_VISIT_DATE](state: State, payload: string) {
    state.visitDate = payload;
  },
  [MutationTypes.GET_RECEIPT_TITLE](state: State, payload: string) {
    state.title = payload;
  },
  [MutationTypes.TOTAL_BILL](state: State, payload: number) {
    state.totalBill = payload;
  },
  [MutationTypes.GET_CURRENCY](state: State, payload: string) {
    state.currency = payload;
  }
};


//actions
type AugmentedActionContext = {
  commit<K extends keyof Mutations>(
    key: K,
    payload: Parameters<Mutations[K]>[1]
  ): ReturnType<Mutations[K]>;
} & Omit<ActionContext<State, State>, "commit">;

// actions interface

export interface Actions {
  [ActionTypes.PROGRESS_BAR](
    { commit }: AugmentedActionContext,
    payload: boolean
  ): void;

  [ActionTypes.AUTH_REQUEST](
    { commit }: AugmentedActionContext,
    payload: string
  ): void;
  
  [ActionTypes.PERMISSION_LIST](
    { commit }: AugmentedActionContext,
    payload: string
  ): void;

  [ActionTypes.AUTH_LOGOUT](
    { commit }: AugmentedActionContext,
    payload: string
  ): void;

  [ActionTypes.OPEN_DIALOG](
    { commit }: AugmentedActionContext,
    payload: boolean
  ): void;

  [ActionTypes.GET_RECEIPT_ID](
    { commit }: AugmentedActionContext,
    payload: string
  ): void;
  
  [ActionTypes.GET_VISIT_DATE](
    { commit }: AugmentedActionContext,
    payload: string
  ): void;

  [ActionTypes.GET_RECEIPT_TITLE](
    { commit }: AugmentedActionContext,
    payload: string
  ): void;

  [ActionTypes.TOTAL_BILL](
    { commit }: AugmentedActionContext,
    payload: number
  ): void; 
  
  [ActionTypes.GET_CURRENCY](
    { commit }: AugmentedActionContext,
    payload: string
  ): void;
}


export const actions: ActionTree<State, State> & Actions = {
  [ActionTypes.PROGRESS_BAR]({ commit }, payload: boolean) {
    commit(MutationTypes.PROGRESS_BAR, payload);
  },
  [ActionTypes.AUTH_REQUEST]({ commit }, payload: string) {
    localStorage.setItem('store_token', payload);
    commit(MutationTypes.AUTH_REQUEST, payload);
  },
  [ActionTypes.PERMISSION_LIST]({ commit }, payload: string) {
    localStorage.setItem('permission_list', payload);
    commit(MutationTypes.PERMISSION_LIST, payload);
  },
  [ActionTypes.AUTH_LOGOUT]({ commit }, payload: string) {
    localStorage.removeItem('store_token');
    localStorage.removeItem('permission_list');
    localStorage.removeItem('currency');
    delete axios.defaults.headers.common["Authorization"];
    commit(MutationTypes.AUTH_LOGOUT, payload);
  },
  [ActionTypes.OPEN_DIALOG]({ commit }, payload: boolean) {
    commit(MutationTypes.OPEN_DIALOG, payload);
  },
  [ActionTypes.GET_RECEIPT_ID]({ commit }, payload: string) {
    commit(MutationTypes.GET_RECEIPT_ID, payload);
  },
  [ActionTypes.GET_VISIT_DATE]({ commit }, payload: string) {
    commit(MutationTypes.GET_VISIT_DATE, payload);
  },
  [ActionTypes.GET_RECEIPT_TITLE]({ commit }, payload: string) {
    commit(MutationTypes.GET_RECEIPT_TITLE, payload);
  },
  [ActionTypes.TOTAL_BILL]({ commit }, payload: number) {
    commit(MutationTypes.TOTAL_BILL, payload);
  },
  [ActionTypes.GET_CURRENCY]({ commit }, payload: string) {
    localStorage.setItem('currency', payload);
    commit(MutationTypes.GET_CURRENCY, payload);
  }
};


// Getters types
export type Getters = {
  getProgressBar(state: State): boolean;
  isAuthenticated(state: State): string;
  gerUserPermissions(state: State): string;
  getReceiptID(state: State): string;
  getVisitDate(state: State): string;
  getReceiptTitle(state: State): string;
  getDiaLogStatus(state: State): boolean;
  getTotalBill(state: State): number;
  getCurrency(state: State): string;
};

//getters

export const getters: GetterTree<State, State> & Getters = {
  getProgressBar: state => {
    return state.progressBar;
  },
  isAuthenticated: state => {
    return state.accessToken;
  },
  gerUserPermissions: state => {
    return state.permissionList;
  },
  getDiaLogStatus: state => {
    return state.dialogState;
  },
  getReceiptID: state => {
    return state.receiptID;
  },
  getVisitDate: state => {
    return state.visitDate;
  },
  getReceiptTitle: state => {
    return state.title;
  },
  getTotalBill: state => {
    return state.totalBill;
  },
  getCurrency: state => {
    return state.currency;
  }
};


//setup store type
export type Store = Omit<
  VuexStore<State>,
  "commit" | "getters" | "dispatch"
> & {
  commit<K extends keyof Mutations, P extends Parameters<Mutations[K]>[1]>(
    key: K,
    payload: P,
    options?: CommitOptions
  ): ReturnType<Mutations[K]>;
} & {
  getters: {
    [K in keyof Getters]: ReturnType<Getters[K]>;
  };
} & {
  dispatch<K extends keyof Actions>(
    key: K,
    payload: Parameters<Actions[K]>[1],
    options?: DispatchOptions
  ): ReturnType<Actions[K]>;
};


export const store = createStore({
  state,
  mutations,
  actions,
  getters,
  plugins: [createLogger()]
});


export function useStore() {
  return store as Store;
}