import React, { useState, useEffect, createContext, useContext } from 'react';
import { MatchTree } from '../models/MatchTree';
import { useAppSelector, useAppDispatch } from '../app/hooks';
import { setMatchTree, selectMatchTree } from '../features/matchTreeSlice';

const withMatchTree = (Wrapped: React.FC) => {
	return (props: any) => {
		const matchTree = useAppSelector(selectMatchTree);
		const dispatch = useAppDispatch();
		const setTree = (matchTree: MatchTree) => dispatch(setMatchTree(matchTree.serialize()));
		return (
			<Wrapped
				matchTree={matchTree}
				setMatchTree={setTree}
				{...props}
			/>
		)
	}
}

export default withMatchTree;