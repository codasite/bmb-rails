import { createSlice, PayloadAction, createSelector } from '@reduxjs/toolkit'
import { RootState } from '../app/store'
import { MatchTree } from '../models/MatchTree'
import { MatchTreeRepr } from '../api/types/bracket'
import { Nullable } from '../../../utils/types'

interface MatchTreeState {
  matchTree: MatchTreeRepr | null
}

const initialState: MatchTreeState = {
  matchTree: null,
}

export const matchTreeSlice = createSlice({
  name: 'matchTree',
  initialState,
  reducers: {
    setMatchTree: (state, action: PayloadAction<Nullable<MatchTreeRepr>>) => {
      state.matchTree = action.payload
    },
  },
})

export const { setMatchTree } = matchTreeSlice.actions

export const selectMatchTree = createSelector(
  (state: RootState) => state.matchTree.matchTree,
  (matchTree) => (matchTree ? MatchTree.deserialize(matchTree) : null)
)

export default matchTreeSlice.reducer
