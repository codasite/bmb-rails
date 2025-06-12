export type BracketClickHandler = (
  button: HTMLButtonElement
) => void | Promise<void>

export interface BracketClickEventHandlerProps {
  /**
   * The children to render
   */
  children: React.ReactNode
  /**
   * Map of button class names to their click handlers
   * Example: { 'wpbb-share-bracket-button': (button) => { ... } }
   */
  handlers: Record<string, BracketClickHandler>
  /**
   * Optional className to add to the wrapper div
   */
  className?: string
}
