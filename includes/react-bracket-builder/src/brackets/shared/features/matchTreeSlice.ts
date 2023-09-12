import { createSlice, PayloadAction, createSelector } from "@reduxjs/toolkit";
import { RootState } from "../app/store";
import { MatchTree } from "../models/MatchTree";
import { MatchRepr } from "../api/types/bracket";
import { Nullable } from "../../../utils/types";


interface MatchTreeState {
	// matchTree: MatchTree | null
	rounds: Nullable<MatchRepr>[][] | null
}

const initialState: MatchTreeState = {
	rounds: null
}

export const matchTreeSlice = createSlice({
	name: 'matchTree',
	initialState,
	reducers: {
		setMatchTree: (state, action: PayloadAction<Nullable<MatchRepr>[][]>) => {
			state.rounds = action.payload;
		}
	}
});

export const { setMatchTree } = matchTreeSlice.actions;

export const selectMatchTree = createSelector(
	(state: RootState) => state.matchTree.rounds,
	rounds => rounds ? MatchTree.deserialize(rounds) : null
)

export default matchTreeSlice.reducer;


