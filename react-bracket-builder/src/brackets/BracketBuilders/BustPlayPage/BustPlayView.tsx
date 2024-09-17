import { useEffect, useState, useContext } from 'react'
import * as Sentry from '@sentry/react'
import { MatchTree } from '../../shared/models/MatchTree'
import { BusterBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import redBracketBg from '../../shared/assets/bracket-bg-red.png'
import { bracketApi } from '../../shared/api/bracketApi'
import { BracketRes, PlayReq, PlayRes } from '../../shared/api/types/bracket'
import { BusterVsBustee } from './BusterVersusBustee'
import { useWindowDimensions } from '../../../utils/hooks'
import { getBracketWidth } from '../../shared/components/Bracket/utils'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'
import { PaginatedBuilder } from '../PaginatedBuilderBase/PaginatedBuilder'
import { getBustTrees } from './utils'
import { BustEndPage, BustBracketPages } from './PaginatedBustBuilder'
import { BracketBusterContext } from './context'
import { WindowDimensionsContext } from '../../shared/context/WindowDimensionsContext'

interface BustPlayViewProps {
  bracket?: BracketRes
  busterTree?: MatchTree
  setBusterTree?: (matchTree: MatchTree) => void
  busteeDisplayName: string
  busteeThumbnail: string
  onButtonClick?: () => void
  buttonText?: string
  processing?: boolean
}

export const BustPlayView = (props: BustPlayViewProps) => {
  const { width: windowWidth, height: windowHeight } = useContext(
    WindowDimensionsContext
  )
  const {
    bracket,
    busterTree,
    setBusterTree,
    busteeDisplayName,
    busteeThumbnail,
    onButtonClick,
    buttonText,
    processing,
  } = props

  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(bracket?.numTeams))

  if (showPaginated && busterTree) {
    return (
      <BracketBusterContext.Provider
        value={{
          busteeDisplayName,
          busteeThumbnail,
          buttonText,
        }}
      >
        <PaginatedBuilder
          EndPageComponent={BustEndPage}
          BracketPagesComponent={BustBracketPages}
          handleSubmit={onButtonClick}
          matchTree={busterTree}
          setMatchTree={setBusterTree}
        />
      </BracketBusterContext.Provider>
    )
  }

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover`}
      style={{
        backgroundImage: `url(${redBracketBg})`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-mx-auto tw-gap-50 tw-pb-100 tw-pt-40`}
      >
        {busterTree && (
          <>
            <BusterVsBustee
              busteeDisplayName={busteeDisplayName}
              busteeThumbnail={busteeThumbnail}
            />
            <BusterBracket
              matchTree={busterTree}
              setMatchTree={setBusterTree}
            />
            <div className="tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ActionButton
                variant="red"
                size="big"
                disabled={!busterTree?.allPicked() || processing}
                onClick={onButtonClick}
              >
                {buttonText}
              </ActionButton>
            </div>
          </>
        )}
      </div>
    </div>
  )
}
