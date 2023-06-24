import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { RootState } from "../../app/store";
import { MatchTree } from "../../bracket/models/MatchTree";

interface MatchTreeState {
	matchTree: MatchTree | null
}

const initialState: MatchTreeState = {
	matchTree: null
}

export const matchTreeSlice = createSlice({
	name: 'matchTree',
	initialState,
	reducers: {
		setMatchTree: (state, action: PayloadAction<MatchTree>) => {
			state.matchTree = action.payload;
		}
	}
});

export const { setMatchTree } = matchTreeSlice.actions;

export const selectMatchTree = (state: RootState) => state.matchTree.matchTree;

export default matchTreeSlice.reducer;


