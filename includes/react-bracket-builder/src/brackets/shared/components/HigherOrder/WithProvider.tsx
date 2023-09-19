import React, { useState, useEffect, createContext, useContext } from 'react';
import { MatchTree } from '../../models/MatchTree';
import { useAppSelector, useAppDispatch } from '../../app/hooks';
import { setMatchTree, selectMatchTree } from '../../features/matchTreeSlice';
import { bracketBuilderStore } from '../../app/store';
import { Provider } from 'react-redux';
import { HOC } from '../types';

export const WithProvider: HOC = (Component: React.FC<any>) => {
	return (props: any) => {
		return (
			<Provider store={bracketBuilderStore}>
				<Component
					{...props}
				/>
			</Provider>
		)
	}
}

export default WithProvider;