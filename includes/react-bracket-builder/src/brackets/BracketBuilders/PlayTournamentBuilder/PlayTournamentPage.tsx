import React, { useState, useEffect } from 'react';
import * as Sentry from '@sentry/react';
import { bracketApi } from '../../shared/api/bracketApi';
import { Nullable } from '../../../utils/types';

import { MatchTree } from '../../shared/models/MatchTree';
import { BracketMeta } from '../../shared/context';
import { WithDarkMode, WithMatchTree, WithBracketMeta, WithProvider } from '../../shared/components/HigherOrder'
import { PlayReq } from '../../shared/api/types/bracket';
import { useWindowDimensions } from '../../../utils/hooks';
import { PaginatedPlayBuilder } from './PaginatedPlayBuilder';
import { PlayBuilder } from './PlayBuilder';

interface PlayPageProps {
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

const PlayPage = (props: PlayPageProps) => {
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
	const { width: windowWidth, height: windowHeight } = useWindowDimensions()
	const showPaginated = windowWidth < 768

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
		const picks = matchTree?.toMatchPicks()
		const tournamentId = tournament?.id
		if (!picks) {
			console.error('no picks')
			return;
		}
		if (!tournamentId) {
			console.error('no tournament id')
			return;
		}
		const playReq: PlayReq = {
			tournamentId: tournament?.id,
			picks: picks,
		}
		console.log('playReq')
		console.log(playReq)

		setProcessing(true)
		bracketApi.createPlay(playReq)
			.then((res) => {
				console.log('res')
				console.log(res)
				setProcessing(false)
				window.location.href = apparelUrl
			})
			.catch((err) => {
				setProcessing(false)
				console.error(err)
				Sentry.captureException(err)
			})


	}

	const handleApparelClickPrint = () => {
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

	const playBuilderProps = {
		matchTree,
		setMatchTree,
		handleApparelClick,
		processing,
		darkMode,
		setDarkMode,
		bracketMeta,
		setBracketMeta,
	}

	if (showPaginated) {
		return <PaginatedPlayBuilder {...playBuilderProps} />
	}

	return <PlayBuilder {...playBuilderProps} />
}

const WrappedPlayPage = WithProvider(WithDarkMode(WithMatchTree(WithBracketMeta(PlayPage))))

export default WrappedPlayPage