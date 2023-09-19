import React, { useState, useEffect, useContext } from 'react';
import * as Sentry from '@sentry/react';
import { bracketApi } from '../../shared/api/bracketApi';
import Spinner from 'react-bootstrap/Spinner'
import { useAppSelector, useAppDispatch } from '../../shared/app/hooks'
import { setMatchTree, selectMatchTree } from '../../shared/features/matchTreeSlice'
import { setNumPages } from '../../shared/features/bracketNavSlice'
import { Nullable } from '../../../utils/types';

import { MatchTree } from '../../shared/models/MatchTree';
import { BracketMeta, BracketMetaContext, DarkModeContext } from '../../shared/context';
import { WithDarkMode, WithMatchTree, WithBracketMeta, WithProvider } from '../../shared/components/HigherOrder'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignore
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { PickableBracket } from '../../shared/components/Bracket/PickableBracket';
import { ThemeSelector } from '../../shared/components';
import { ActionButton } from '../../shared/components/ActionButtons';

interface UserBracketProps {
	apparelUrl: string;
	bracketStylesheetUrl: string;
	tournament?: any;
	template?: any;
	matchTree?: MatchTree;
	setMatchTree?: (matchTree: MatchTree) => void;
	darkMode?: boolean;
	setDarkMode?: (darkMode: boolean) => void;
	bracketMeta?: BracketMeta
	setBracketMeta?: (bracketMeta: BracketMeta) => void
}

interface RenderBracketProps {
	matchTree: MatchTree | null;
	setMatchTree: (matchTree: MatchTree) => void;
	canPick: boolean;
	bracketTitle?: string;
}


const PlayTournamentBuilder = (props: UserBracketProps) => {
	const {
		tournament,
		template,
		apparelUrl,
		bracketStylesheetUrl,
		matchTree,
		setMatchTree,
		bracketMeta,
		setBracketMeta,
		darkMode,
		setDarkMode,
	} = props;

	const [processing, setProcessing] = useState(false);

	useEffect(() => {
		let tree: Nullable<MatchTree> = null
		if (tournament && tournament.bracketTemplate) {
			const template = tournament.bracketTemplate;
			const numTeams = template.numTeams;
			const matches = template.matches;
			setBracketMeta?.({ title: tournament.title, date: '2021' })
			tree = MatchTree.fromMatchRes(numTeams, matches);
			// Better to have separate components for template and tournament
		} else if (template) {
			const numTeams = template.numTeams;
			const matches = template.matches;
			tree = MatchTree.fromMatchRes(numTeams, matches);
			setBracketMeta?.({ title: template.title, date: '2021' })
		}
		if (tree && setMatchTree) {
			setMatchTree(tree)
		}
	}, []);

	// useEffect(() => {
	// 	if (matchTree) {
	// 		// For a paginated bracket there are two pages for all but the last round, plus a landing page and a final page
	// 		const numPages = matchTree.rounds.length * 2 + 1;
	// 		dispatch(setNumPages(numPages))
	// 	}
	// }, [matchTree])

	const buildPrintHTML = (innerHTML: string, styleUrl: string, inchHeight: number, inchWidth: number,) => {
		const printArea = buildPrintArea(innerHTML, inchHeight, inchWidth)
		// const stylesheet = 'https://backmybracket.com/wp-content/plugins/wp-bracket-builder/includes/react-bracket-builder/build/index.css'
		const stylesheet = 'https://wpbb-stylesheets.s3.amazonaws.com/index.css'
		return `
			<html>
				<head>
					<link rel='stylesheet' href='${stylesheet}' />
				</head>
			<body style='margin: 0; padding: 0;'>
				${printArea}
			</body>
			</html>
		`
	}

	const buildPrintArea = (innerHTML: string, inchHeight: number, inchWidth: number) => {
		const width = inchWidth * 96;
		const height = inchHeight * 96;
		return `
			<div class='wpbb-bracket-print-area' style='height: ${height}px; width: ${width}px; background-color: transparent'>
				${innerHTML}
			</div>
		`
	}

	const getHTML = (): string => {
		const bracketEl = document.getElementsByClassName('wpbb-bracket')[0]
		const bracketHTML = bracketEl.outerHTML
		const bracketCss = bracketStylesheetUrl
		const html = buildPrintHTML(bracketHTML, bracketCss, 16, 12)
		return html
	}

	const minify = (html: string) => {
		return html.replace(/[\n\t]/g, '').replace(/"/g, "'")
	}

	const handleApparelClick = () => {
		// const id = bracketId || bracketRes?.id;
		// if (!id || !matchTree) {
		// 	console.error('no bracket id or match tree')
		// 	return;
		// }
		const html = getHTML()

		const parser = new DOMParser();
		const doc = parser.parseFromString(html, 'text/html');
		const bracketEl = doc.getElementsByClassName('wpbb-bracket')[0]
		const printArea = doc.getElementsByClassName('wpbb-bracket-print-area')[0]

		// if we were in dark mode, remove it to get the light mode version
		bracketEl.classList.remove('wpbb-dark-mode')
		const lightModeTopHTML = minify(doc.documentElement.outerHTML)
		printArea.classList.add('wpbb-print-center')
		const lightModeCenterHTML = minify(doc.documentElement.outerHTML)
		bracketEl.classList.add('wpbb-dark-mode')
		const darkModeCenterHTML = minify(doc.documentElement.outerHTML)
		printArea.classList.remove('wpbb-print-center')
		const darkModeTopHTML = minify(doc.documentElement.outerHTML)

		// Random key to link the two images together
		const key = Math.random().toString(36).substring(7);
		const promises = [
			bracketApi.htmlToImage({ html: darkModeTopHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `dark`, bracketPlacement: 'top', s3Key: `bracket-${key}-dark-top.png` }),
			bracketApi.htmlToImage({ html: lightModeTopHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `light`, bracketPlacement: 'top', s3Key: `bracket-${key}-light-top.png` }),
			bracketApi.htmlToImage({ html: darkModeCenterHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `dark`, bracketPlacement: 'center', s3Key: `bracket-${key}-dark-center.png` }),
			bracketApi.htmlToImage({ html: lightModeCenterHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `light`, bracketPlacement: 'center', s3Key: `bracket-${key}-light-center.png` }),
		]
		setProcessing(true)
		Promise.all(promises).then((res) => {
			// console.log('res')
			// console.log(res)
			// setProcessing(false)
			window.location.href = apparelUrl
		}).catch((err) => {
			setProcessing(false)
			console.error(err)
			Sentry.captureException(err)
		})
	}

	return (
		<div className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${darkMode ? ' tw-dark' : ''}`} style={{ 'backgroundImage': `url(${darkMode ? darkBracketBg : lightBracketBg})` }}>
			{matchTree &&
				<div className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}>
					<div className='tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center'>
						<ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
					</div>
					<PickableBracket
						matchTree={matchTree}
						setMatchTree={setMatchTree}
					/>
					<div className='tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center'>
						<ActionButton
							variant='big-green'
							darkMode={darkMode}
							onClick={handleApparelClick}
							disabled={processing || !matchTree.allPicked()}
						>Add to Apparel</ActionButton>
					</div>
				</div>
			}

		</div>
	)
}

const WrappedPlayBuilder = WithProvider(WithDarkMode(WithMatchTree(WithBracketMeta(PlayTournamentBuilder))))

export default WrappedPlayBuilder