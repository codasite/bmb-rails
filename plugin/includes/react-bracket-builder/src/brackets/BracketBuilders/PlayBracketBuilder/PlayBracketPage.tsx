import React, { useEffect, useState } from 'react'
import * as Sentry from '@sentry/react'
import { bracketApi } from '../../shared/api/bracketApi'
import { Nullable } from '../../../utils/types'
import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
import { BracketRes, PlayReq } from '../../shared/api/types/bracket'
import { useWindowDimensions } from '../../../utils/hooks'
import { PaginatedPlayBuilder } from './PaginatedPlayBuilder/PaginatedPlayBuilder'
import { PlayBuilder } from './PlayBuilder'
import { getBracketWidth } from '../../shared/utils'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'

interface PlayPageProps {
  apparelUrl: string
  bracketStylesheetUrl: string
  bracket?: BracketRes
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
}

const PlayPage = (props: PlayPageProps) => {
  console.log('PlayPage')
  const {
    bracket,
    apparelUrl,
    bracketStylesheetUrl,
    matchTree,
    setMatchTree,
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
  } = props

  const [processing, setProcessing] = useState(false)
  const { width: windowWidth, height: windowHeight } = useWindowDimensions()
  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(bracket?.numTeams))

  useEffect(() => {
    let tree: Nullable<MatchTree> = null
    if (bracket) {
      const numTeams = bracket.numTeams
      const matches = bracket.matches
      tree = MatchTree.fromMatchRes(numTeams, matches)
      setBracketMeta?.({ title: bracket.title, date: bracket.date })
    }
    if (tree && setMatchTree) {
      setMatchTree(tree)
    }
  }, [])

  const handleApparelClick = () => {
    console.log('handleApparelClick')
    const picks = matchTree?.toMatchPicks()
    console.log(picks)
    const bracketId = bracket?.id
    console.log(bracketId)
    if (!picks) {
      const msg = 'Cannot create play. Missing picks'
      console.error(msg)
      Sentry.captureException(msg)
      return
    }
    const playReq: PlayReq = {
      bracketId: bracket?.id,
      picks: picks,
      generateImages: true,
    }

    console.log(playReq)
    setProcessing(true)
    bracketApi
      .createPlay(playReq)
      .then((res) => {
        console.log(res)
        // window.location.href = apparelUrl
      })
      .catch((err) => {
        console.error('error: ', err)
        Sentry.captureException(err)
      })
      .finally(() => {
        setProcessing(false)
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

const WrappedPlayPage = WithProvider(
  WithDarkMode(WithMatchTree(WithBracketMeta(PlayPage)))
)

export default WrappedPlayPage
