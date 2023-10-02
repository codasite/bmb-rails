import { createContext } from 'react';

export interface BracketMeta {
	title?: string,
	date?: string,
}

export const DarkModeContext = createContext(false)
export const BracketMetaContext = createContext<BracketMeta>({})

