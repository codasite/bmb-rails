import { createContext } from 'react';
import { BracketDetials } from './components/Bracket/Bracket';

export const DarkModeContext = createContext(false)

export const BracketContext = createContext<BracketDetials | undefined>(undefined)
