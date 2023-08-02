import { createSlice, PayloadAction, createSelector } from "@reduxjs/toolkit";
import { RootState } from "../app/store";
import { MatchTree } from "../models/MatchTree";
import { RoundRes } from "../api/types/bracket";

interface MatchTreeState {
	// matchTree: MatchTree | null
	rounds: RoundRes[] | null
}

const initialState: MatchTreeState = {
	rounds: null
}

export const matchTreeSlice = createSlice({
	name: 'matchTree',
	initialState,
	reducers: {
		setMatchTree: (state, action: PayloadAction<RoundRes[]>) => {
			console.log('setMatchTree', action.payload)
			state.rounds = action.payload;
		}
	}
});

export const { setMatchTree } = matchTreeSlice.actions;

export const selectMatchTree = createSelector(
	(state: RootState) => state.matchTree.rounds,
	rounds => rounds ? MatchTree.fromRounds(rounds) : null
)

export default matchTreeSlice.reducer;


