import { Nullable } from '../../../../utils/types';

interface Team {
	name: string;
}

export interface TeamRes extends Team {
	id: Nullable<number>;
}

export interface TeamReq extends Team { }

interface Match {
	index: number;
}

export interface MatchRes extends Match {
	id: Nullable<number>;
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

interface BracketBase {
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

export interface BracketRes extends BracketBase {
	id: number;
	createdAt: phpDate;
	numSubmissions: number;
	rounds: RoundRes[];
}

export interface BracketReq extends BracketBase {
	rounds: RoundReq[];
}

export interface SubmissionRes {
	id: number;
	// createdAt: phpDate;
	bracketId: number;
	customerId: number;
	name: string;
	rounds: RoundRes[];
}

export interface SubmissionReq {
	bracketId: number;
	name: string;
	html: string;
	rounds?: SubmissionRoundReq[];
}

export interface SubmissionRoundReq {
	matches: Nullable<SubmissionMatchReq>[];
}

export interface SubmissionMatchReq {
	result: Nullable<SubmissionTeamReq>;
}

export interface SubmissionTeamReq {
	id: number;
}

export interface HTMLtoImageReq {
	html: string;
	inchHeight: number;
	inchWidth: number;
	deviceScaleFactor?: number;
	themeMode?: string;
	bracketPlacement?: string;
	key?: string;
	s3Key?: string;
}

export interface HTMLtoImageRes {
	imageUrl: string;
}

export interface MatchResV2 {
	id: number;
	roundIndex: number;
	matchIndex: number;
	team1?: TeamRes;
	team2?: TeamRes;
}
