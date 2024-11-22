// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { render } from 'react-dom'
import { ScaledSpan } from '../../brackets/shared/components/TeamSlot/ScaledSpan'

export const insertLeaderboardTeamName = (WpbbAppObj: any) => {
  //get all elements with class name "wpbb-lb-winning-team-name-container"
  const divs = document.getElementsByClassName(
    'wpbb-lb-winning-team-name-container'
  )
  for (const d of divs) {
    const teamName = d.getAttribute('data-team-name')
    const targetWidth = parseInt(d.getAttribute('data-target-width'))
    render(
      <div
        className="tw-flex tw-justify-center"
        style={{ maxWidth: targetWidth }}
      >
        <ScaledSpan
          className="tw-flex tw-justify-center"
          targetWidth={targetWidth}
        >
          {teamName}
        </ScaledSpan>
      </div>,
      d
    )
  }
}
