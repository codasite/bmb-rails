import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { RootState } from "../app/store";
import { MatchTree } from "../../bracket/models/MatchTree";
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
			state.rounds = action.payload;
		}
	}
});

export const { setMatchTree } = matchTreeSlice.actions;

// export const selectMatchTree = (state: RootState) => state.matchTree.matchTree;
// export const selectMatchTree = (state: RootState) => state.matchTree.rounds;
export const selectMatchTree = (state: RootState) => state.matchTree.rounds ? MatchTree.fromRounds(state.matchTree.rounds) : null;

export default matchTreeSlice.reducer;


