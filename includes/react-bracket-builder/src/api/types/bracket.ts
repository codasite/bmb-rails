import { Nullable } from '../../utils/types';

interface Team {
	name: string;
}

export interface TeamRes extends Team {
	id: number;
}

export interface TeamReq extends Team { }

interface Match {
	index: number;
}

export interface MatchRes extends Match {
	id: number;
	team1: Nullable<TeamRes>;
	team2: Nullable<TeamRes>;
	result: Nullable<TeamRes>;
}

export interface MatchReq extends Match {
	team1: Nullable<TeamReq>;
	team2: Nullable<TeamReq>;
	result: Nullable<TeamReq>;
}

interface Round {
	name: string;
	depth: number;
}

export interface RoundRes extends Round {
	id: number;
	matches: Nullable<MatchRes>[];
}

export interface RoundReq extends Round {
	matches: Nullable<MatchReq>[];
}

interface Bracket {
	name: string;
	active: boolean;
	numRounds: number;
	numWildcards: number;
	wildcardPlacement: number;
}

interface phpDate {
	date: string;
	timezone_type: number;
	timezone: string;
}

export interface BracketRes extends Bracket {
	id: number;
	createdAt: phpDate;
	numSubmissions: number;
	rounds: RoundRes[];
}

export interface BracketReq extends Bracket {
	rounds: RoundReq[];
}

export interface UserTeamReq {
	id: number;
}

export interface UserMatchReq {
	result: Nullable<UserTeamReq>;
}

export interface UserRoundReq {
	matches: Nullable<UserMatchReq>[];
}

export interface UserBracketReq {
	bracketId: number;
	name: string;
	rounds: UserRoundReq[];
}

export interface SubmissionRes {
	id: number;
	// createdAt: phpDate;
	bracketId: number;
	customerId: number;
	name: string;
	rounds: RoundRes[];
}
