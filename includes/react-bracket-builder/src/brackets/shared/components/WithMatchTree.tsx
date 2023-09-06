import React, { useState, useEffect, createContext, useContext } from 'react';
import { MatchTree } from '../models/MatchTree';
import { useAppSelector, useAppDispatch } from '../app/hooks';
import { setMatchTree, selectMatchTree } from '../features/matchTreeSlice';

export const WithMatchTree = (Wrapped) => {
	const matchTree = useAppSelector(selectMatchTree);
	const dispatch = useAppDispatch();
	const setTree = (matchTree: MatchTree) => dispatch(setMatchTree(matchTree.toSerializable()));
	return (props: any) => {
		return (
			<Wrapped
				matchTree={matchTree}
				setMatchTree={setTree}
				{...props}
			/>
		)
	}
}