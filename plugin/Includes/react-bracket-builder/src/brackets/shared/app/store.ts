import { configureStore } from '@reduxjs/toolkit'
import matchTreeReducer from '../features/matchTreeSlice'
import bracketNavReducer from '../features/bracketNavSlice'

export const bracketBuilderStore = configureStore({
  reducer: {
    matchTree: matchTreeReducer,
    bracketNav: bracketNavReducer,
  },
})

// Infer the `RootState` and `AppDispatch` types from the store itself
export type RootState = ReturnType<typeof bracketBuilderStore.getState>
// Inferred type: {posts: PostsState, comments: CommentsState, users: UsersState}
export type AppDispatch = typeof bracketBuilderStore.dispatch
