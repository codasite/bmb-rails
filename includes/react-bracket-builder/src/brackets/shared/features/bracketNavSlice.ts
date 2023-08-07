import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { RootState } from "../app/store";

interface BracketNavState {
	numPages: number
	currentPage: number
}

const initialState: BracketNavState = {
	numPages: 0,
	currentPage: 0
}

export const bracketNavSlice = createSlice({
	name: 'bracketNav',
	initialState,
	reducers: {
		setNumPages: (state, action: PayloadAction<number>) => {
			state.numPages = action.payload;
		},
		nextPage: (state) => {
			state.currentPage = Math.min(state.currentPage + 1, state.numPages - 1);
		},
		prevPage: (state) => {
			state.currentPage = Math.max(state.currentPage - 1, 0);
		},
		setPage: (state, action: PayloadAction<number>) => {
			state.currentPage = action.payload;
		}
	}
});


export const { setNumPages, nextPage, prevPage, setPage } = bracketNavSlice.actions;

export const selectNumPages = (state: RootState) => state.bracketNav.numPages;
export const selectCurrentPage = (state: RootState) => state.bracketNav.currentPage;

export default bracketNavSlice.reducer;

